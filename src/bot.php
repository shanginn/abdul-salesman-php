<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Cycle\ORM\EntityManager;
use Http\Client\Exception\HttpException;

use function React\Async\await;

use Shanginn\AbdulSalesman\Anthropic\Anthropic;
use Shanginn\AbdulSalesman\Anthropic\AnthropicClient;
use Shanginn\AbdulSalesman\Anthropic\Message\KnownToolUseContent;
use Shanginn\AbdulSalesman\Anthropic\Message\Message;
use Shanginn\AbdulSalesman\Anthropic\Message\Role;
use Shanginn\AbdulSalesman\Anthropic\Message\TextContent;
use Shanginn\AbdulSalesman\Anthropic\Message\ToolChoice;
use Shanginn\AbdulSalesman\Character\InteractionSchema;
use Shanginn\AbdulSalesman\Character\InteractionTool;
use Shanginn\AbdulSalesman\EchoLogger;
use Shanginn\AbdulSalesman\Entity;
use Shanginn\AbdulSalesman\OneMessageAtOneTimeMiddleware;
use Shanginn\AbdulSalesman\StartCommandHandler;

use Shanginn\TelegramBotApiBindings\Types\Update;
use Shanginn\TelegramBotApiFramework\TelegramBot;

Dotenv\Dotenv::createImmutable(__DIR__)->load();

$botToken = $_ENV['TELEGRAM_BOT_TOKEN'];
assert(is_string($botToken), 'Bot token must be a string');

$anthropicApiKey = $_ENV['ANTHROPIC_API_KEY'];
assert(is_string($anthropicApiKey), 'Anthropic API key must be a string');

$bot = new TelegramBot($botToken, logger: new EchoLogger());

$ant = new Anthropic(
    new AnthropicClient($anthropicApiKey),
);

[
    'abdul'                     => $abdul,
    'systemPrompt'              => $systemPrompt,
    'finalSystemPromptTemplate' => $finalSystemPromptTemplate,
] = require __DIR__ . '/../config/config.php';

$orm = require __DIR__ . '/../config/orm.php';
$em  = new EntityManager($orm);

/** @var Entity\Message[] $messages */
$messages = $orm->getRepository(Entity\Message::class)->findAll();

$states = [];

$botInfo = await($bot->api->getMe());

foreach ($messages as $message) {
    $chatId = $message->chatId;

    if (!isset($states[$chatId])) {
        $states[$chatId] = [];
    }

    $states[$chatId][] = new Message(
        role: $message->fromUserId === (string) $botInfo->id ? Role::ASSISTANT : Role::USER,
        content: $message->text,
    );

    if ($message->isFinishMessage) {
        $states[$chatId] = [];
    }
}

$gameLoopHandler = function (Update $update, TelegramBot $bot) use (
    $botInfo,
    &$states,
    &$gameLoopHandler,
    $ant,
    $abdul,
    $systemPrompt,
    $finalSystemPromptTemplate,
    $em
): void {
    await($bot->api->sendChatAction(
        chatId: $update->message->chat->id,
        action: 'typing',
    ));

    $chatId = $update->message->chat->id;

    if (!isset($states[$chatId])) {
        $states[$chatId] = [];
    }

    $states[$chatId][] = new Message(
        content: $update->message->text,
    );

    $retries = 0;

    $hasText = false;
    do {
        try {
            $prompt = $systemPrompt;

            if ($retries > 0) {
                $prompt .= <<<TXT
                    This is the {$retries} retry.
                    please think very carefully and say something in the `Speech and Actions`.
                    After fifth reply the game will be over...
                    TXT;
            }

            $response = $ant->message(
                system: $prompt,
                messages: $states[$chatId],
                tools: [InteractionTool::class],
                toolChoice: ToolChoice::useTool(InteractionTool::class),
            );
        } catch (HttpException $e) {
            $text = '*Абдул почувствовал себя плохо и поспешно удалился. Попробуйте найти его позже*';

            $states[$chatId] = [];

            $em->persist(new Entity\Message(
                text: $text,
                chatId: $chatId,
                createdAt: new DateTimeImmutable(),
                fromUserId: $botInfo->id,
                fromUsername: $botInfo->username,
                isFinishMessage: true,
            ))->run();

            await($bot->api->sendMessage(
                chatId: $update->message->chat->id,
                text: $text,
            ));

            return;
        }

        foreach ($response->content as $content) {
            if ($content instanceof TextContent || (
                $content instanceof KnownToolUseContent
                    && $content->input instanceof InteractionSchema
                    && $content->input->speechAndActions !== null
            )) {
                $hasText = true;

                break 2;
            }
        }

        await(\React\Promise\Timer\sleep(0.5 * $retries));
    } while (++$retries <= 5);

    if (!$hasText) {
        $text = '*Абдул долго думал, но так и не придумал, что вам ответить*';

        $states[$chatId][] = new Message(
            role: Role::ASSISTANT,
            content: $text,
        );

        $em->persist(new Entity\Message(
            text: $text,
            chatId: $chatId,
            createdAt: new DateTimeImmutable(),
            fromUserId: $botInfo->id,
            fromUsername: $botInfo->username,
        ))->run();

        await($bot->api->sendMessage(
            chatId: $update->message->chat->id,
            text: $text,
        ));

        return;
    }

    $em->persist(new Entity\Message(
        text: $update->message->text,
        chatId: $chatId,
        createdAt: new DateTimeImmutable(),
        fromUserId: $update->message->from->id,
        fromUsername: $update->message->from->username,
    ))->run();

    foreach ($response->content as $content) {
        $text        = null;
        $messageText = null;
        $exitReason  = null;

        if ($content instanceof TextContent) {
            $messageText = $text = $content->text;
        }

        if ($content instanceof KnownToolUseContent && $content->input instanceof InteractionSchema) {
            $text = $content->input->speechAndActions;

            $messageText = <<<TXT
                <INTERNAL_MONOLOGUE>{$content->input->internalMonologue}</INTERNAL_MONOLOGUE>

                "{$content->input->speechAndActions}"
                TXT;

            if ($content->input->desireToLeave >= 0.8) {
                $exitReason = "My desire to leave is {$content->input->desireToLeave}/1.0";
            } elseif ($content->input->priceIsAgreed) {
                $exitReason = 'The price is agreed and I am willing to sell the Gem of the Desert';
            }
        }

        if ($exitReason !== null) {
            $summarizedMessage = '';

            /** @var Message $message */
            foreach ($states[$chatId] as $message) {
                $role = $message->role === Role::USER ? 'Игрок' : $abdul->name;
                $summarizedMessage .= "{$role}: ```\n{$message->content}\n``` \n";
            }

            $exitResponse = $ant->message(
                system: strtr($finalSystemPromptTemplate, [
                    '{{exitReason}}' => $exitReason,
                ]),
                messages: [
                    new Message(
                        role: Role::USER,
                        content: $summarizedMessage,
                    ),
                ],
            );

            dump($exitResponse);

            $text .= "\n";

            if (count($exitResponse->content) === 0 || !$exitResponse->content[0] instanceof TextContent) {
                $text .= "{$abdul->name} ушёл не прощаясь.";
            } else {
                $text .= $exitResponse->content[0]->text;
            }

            await($bot->api->sendMessage(
                chatId: $update->message->chat->id,
                text: $text,
            ));

            $states[$chatId] = [];

            $em->persist(new Entity\Message(
                text: $text,
                chatId: $chatId,
                createdAt: new DateTimeImmutable(),
                fromUserId: $botInfo->id,
                fromUsername: $botInfo->username,
                isFinishMessage: true,
            ))->run();

            await($bot->api->sendMessage(
                chatId: $update->message->chat->id,
                text: <<<'TXT'
                    <b>Игра окончена!
                    Спасибо за игру. Начните новую игру, написав что-нибудь.</b>
                    TXT,
                parseMode: 'HTML',
            ));

            return;
        }

        if ($text !== null) {
            $states[$chatId][] = new Message(
                role: Role::ASSISTANT,
                content: $messageText,
            );

            $em->persist(new Entity\Message(
                text: $messageText,
                chatId: $chatId,
                createdAt: new DateTimeImmutable(),
                fromUserId: $botInfo->id,
                fromUsername: $botInfo->username,
            ))->run();

            await($bot->api->sendMessage(
                chatId: $update->message->chat->id,
                text: $text,
            ));
        }
    }
};

$bot->addHandler($gameLoopHandler)
    ->supports(fn (Update $update) => isset($update->message->text) && $update->message->entities === null)
    ->middleware(new OneMessageAtOneTimeMiddleware())
;

$bot->addHandler(new StartCommandHandler())
    ->supports(StartCommandHandler::supports(...));

$bot->run();
