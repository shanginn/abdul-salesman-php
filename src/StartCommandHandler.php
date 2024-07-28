<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman;

use function React\Async\await;

use React\Promise\Timer;

use Shanginn\TelegramBotApiBindings\Types\LinkPreviewOptions;
use Shanginn\TelegramBotApiBindings\Types\Update;
use Shanginn\TelegramBotApiFramework\Handler\AbstractStartCommandHandler;
use Shanginn\TelegramBotApiFramework\TelegramBot;

class StartCommandHandler extends AbstractStartCommandHandler
{
    public function handle(Update $update, TelegramBot $bot)
    {
        await($bot->api->sendChatAction(
            chatId: $update->message->chat->id,
            action: 'typing'
        ));

        await($bot->api->sendPhoto(
            chatId: $update->message->chat->id,
            photo: 'AgACAgIAAxkBAAJQJGal0Sp5g4OdtLgkEKN0gt_1mTbeAAJp3zEb8AwwSZ5MsaQLI8KLAQADAgADeQADNQQ',
            caption: <<<'TXT'
                Вы оказываетесь в солнечном Каире, на шумных и оживлённых улицах рынка.
                Золотистые лучи солнца освещают яркие торговые ряды, где продавцы предлагают экзотические фрукты,
                специи и изделия ручной работы. В воздухе царит атмосфера суеты и веселья:
                гул голосов, смех детей и далёкий призыв муэдзина.
                Вдалеке возвышаются минареты и древние здания, создавая неповторимый фон этого древнего города.
                Вам предстоит погрузиться в эту пёструю толпу, следуя по пути, который ведёт вглубь рынка.
                TXT
        ));

        await($bot->api->sendChatAction(
            chatId: $update->message->chat->id,
            action: 'typing'
        ));

        await(Timer\sleep(1));

        await($bot->api->sendPhoto(
            chatId: $update->message->chat->id,
            photo: 'AgACAgIAAxkBAAJQJWal0TIprEJbXdsCW6eM89vBMcqYAAL93TEblsUxSXIbICFRyZf4AQADAgADeQADNQQ',
            caption: <<<'TXT'
                Продвигаясь через многолюдные улицы, Вы оказываетесь на узком извилистом переулке,
                отдалённом от главных дорог Каира. Стены домов украшены сложными узорами и старыми фресками,
                рассказывающими истории прошлого. Подвесные фонари мягко покачиваются на ветру,
                отбрасывая танцующие тени на мощёную дорожку. Здесь гораздо тише:
                лишь мягкий шёпот далёких разговоров и редкий звон металла от кузницы.
                В воздухе витает аромат благовоний и экзотических трав,
                перемешанный с тонким сладким запахом цветов из соседнего сада.
                В конце переулка Вы замечаете деревянную вывеску, указывающую путь к магазину ковров Абдула.
                TXT
        ));

        await($bot->api->sendChatAction(
            chatId: $update->message->chat->id,
            action: 'typing'
        ));

        await(Timer\sleep(1));

        await($bot->api->sendPhoto(
            chatId: $update->message->chat->id,
            photo: 'AgACAgIAAxkBAAJQJmal0Th2mWJuaHKa8u6-nRHa8LzNAAL-3TEblsUxSdGX9oVJ0Z4MAQADAgADeQADNQQ',
            caption: <<<'TXT'
                Наконец, Вы подходите к входу в магазин ковров Абдула, спрятанный в лабиринте улиц Каира.
                Деревянная дверь, чуть приоткрытая, украшена сложными геометрическими узорами и бронзовыми элементами.
                Вход обрамляют пышные висящие лозы и яркие цветущие растения, создавая гостеприимную,
                но загадочную атмосферу.
                Из приоткрытой двери доносится запах богатых пряностей и слабый аромат сандала.
                Внутри мягкий свет фонарей освещает роскошные ковры, украшающие стены и пол, намекая на сокровища,
                которые хранятся внутри. Вы чуть стоите на пороге, и уверенно шагаете в мир роскоши и историй...
                
                <i>Изображения созданы с помощью нейросети <a href="https://t.me/genera4_bot?start=abdul">генерач</a></i>
                TXT,
            parseMode: 'html',
        ));

        await(Timer\sleep(1));

        await($bot->api->sendMessage(
            chatId: $update->message->chat->id,
            text: <<<'HTML'
                <b>Напишите что-нибудь, чтобы начать диалог с Абдулом</b>
                HTML,
            parseMode: 'html',
            linkPreviewOptions: new LinkPreviewOptions(
                isDisabled: true
            )
        ));
    }
}