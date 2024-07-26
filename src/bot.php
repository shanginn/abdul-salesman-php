<?php

require __DIR__ . '/../vendor/autoload.php';

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
use Shanginn\AbdulSalesman\Character\Mood;
use Shanginn\AbdulSalesman\Character\Person;
use Shanginn\AbdulSalesman\Character\Personality;
use Shanginn\AbdulSalesman\EchoLogger;
use Shanginn\AbdulSalesman\OneMessageAtOneTimeMiddleware;
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
    'abdul' => $abdul,
    'systemPrompt' => $systemPrompt,
    'finalSystemPromptTemplate' => $finalSystemPromptTemplate,
] = require __DIR__ . '/config.php';

$states = [];

$gameLoopHandler = function (Update $update, TelegramBot $bot) use (
    &$states,
    &$gameLoopHandler,
    $ant,
    $abdul,
    $systemPrompt,
    $finalSystemPromptTemplate,
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

    $response = $ant->message(
        system: $systemPrompt,
        messages: $states[$chatId],
        tools: [InteractionTool::class],
        toolChoice: ToolChoice::useTool(InteractionTool::class),
    );

    dump($response);

    $messageSent = false;

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

            await($bot->api->sendMessage(
                chatId: $update->message->chat->id,
                text: $text,
            ));

            $messageSent = true;
        }
    }

    if (!$messageSent) {
        $gameLoopHandler($update, $bot);
    }
};

$bot->addHandler($gameLoopHandler)
    ->supports(fn (Update $update) => isset($update->message->text))
    ->middleware(new OneMessageAtOneTimeMiddleware())
;

$bot->run();
