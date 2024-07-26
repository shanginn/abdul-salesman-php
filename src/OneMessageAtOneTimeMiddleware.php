<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman;

use function React\Async\await;

use Shanginn\TelegramBotApiBindings\Types\ReplyParameters;
use Shanginn\TelegramBotApiBindings\Types\Update;
use Shanginn\TelegramBotApiFramework\Handler\UpdateHandlerInterface;
use Shanginn\TelegramBotApiFramework\Middleware\MiddlewareInterface;

use Shanginn\TelegramBotApiFramework\TelegramBot;

class OneMessageAtOneTimeMiddleware implements MiddlewareInterface
{
    private array $activeMessages = [];

    public function process(Update $update, UpdateHandlerInterface $handler, TelegramBot $bot): void
    {
        $chatId = $update->message->chat->id;
        if (array_key_exists($chatId, $this->activeMessages)) {
            await($bot->api->sendMessage(
                chatId: $chatId,
                text: <<<'TXT'
                    <b>Это сообщение не будет включено в разговор, 
                    пожалуйста, дождитесь ответа бота на предыдущее сообщение
                    перед отправкой следующего.</b>
                    TXT,
                replyParameters: new ReplyParameters(
                    messageId: $update->message->messageId,
                    allowSendingWithoutReply: true,
                ),
                parseMode: 'HTML',
            ));

            return;
        }

        $this->activeMessages[$chatId] = true;

        $handler->handle($update, $bot);

        unset($this->activeMessages[$chatId]);
    }
}
