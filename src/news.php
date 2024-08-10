<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Cycle\ORM\EntityManager;
use Cycle\ORM\ORMInterface;

use function React\Async\await;

use Shanginn\AbdulSalesman\EchoLogger;
use Shanginn\AbdulSalesman\Entity;

use Shanginn\TelegramBotApiFramework\TelegramBot;

$message = <<<'TXT'
    Дорогие друзья! Я благодарю вас за участие в этой увлекательной игре!

    С Абдулом поговорило около 60 человек, и вместе вы отправили более 1200 сообщений!
    Самый активный игрок отправил 228 сообщений, а в среднем каждый из вас отправлял около 40 сообщений.
    На оплату работы Абдула ушло около 5000 рублей.

    Надеюсь, играть в эту игру вам было также весело, как и мне её делать.
    На этом игра завершена. Спасибо, что были с нами, и до новых встреч!

    А новые приключения уже в разработке! Чтобы узнать, когда они будут, вы можете погадать у <a href="t.me/Mystaro_bot">Мадам Мистаро</a>, либо подписаться на мой канал и ждать новостей там: @shanginn_live

    (прошу прощения за повторное обращение, если вы уже получали это сообщение, дебажим в проде 👌)
    TXT;

/** @var ORMInterface $orm */
$orm = require __DIR__ . '/../config/orm.php';
$em  = new EntityManager($orm);

/** @var Entity\Message[] $messages */
$messages = $orm->getRepository(Entity\Message::class)->findAll();

$users = array_reduce($messages, function (array $users, Entity\Message $message) {
    $users[$message->fromUserId] = true;

    return $users;
}, []);

unset($users[7208371640]);

$users = array_keys($users);

Dotenv\Dotenv::createImmutable(__DIR__, names: '.env.prod')->load();

$botToken = $_ENV['TELEGRAM_BOT_TOKEN'];
assert(is_string($botToken), 'Bot token must be a string');

$bot = new TelegramBot($botToken, logger: new EchoLogger());

foreach ($users as $user) {
    try {
        $result = await($bot->api->sendMessage($user, $message, parseMode: 'HTML'));
    } catch (Throwable $e) {
        $result = $e->getMessage();
    }

    dump('Processing user ' . $user . ': ');
    dump($result);
}