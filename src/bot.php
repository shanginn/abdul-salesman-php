<?php
require __DIR__ . '/../vendor/autoload.php';

use Shanginn\AbdulSalesman\Anthropic\Message\KnownToolUseContent;
use Shanginn\AbdulSalesman\Anthropic\Message\MessageRequest;
use Shanginn\AbdulSalesman\Anthropic\Message\TextContent;
use Shanginn\AbdulSalesman\Anthropic\Message\ToolChoice;
use Shanginn\AbdulSalesman\Character\InteractionSchema;
use Shanginn\AbdulSalesman\Character\InteractionTool;
use Shanginn\AbdulSalesman\Character\Mood;
use Shanginn\AbdulSalesman\Character\Person;
use Shanginn\AbdulSalesman\Character\Personality;
use Shanginn\AbdulSalesman\EchoLogger;
use Shanginn\AbdulSalesman\Anthropic\Anthropic;
use Shanginn\AbdulSalesman\Anthropic\AnthropicClient;
use Shanginn\AbdulSalesman\Anthropic\Message\Message;
use Shanginn\AbdulSalesman\Anthropic\Message\Role;
use Shanginn\AbdulSalesman\Tarot\DoTarotReadingAbstractTool;
use Shanginn\TelegramBotApiBindings\Types\InlineKeyboardButton;
use Shanginn\TelegramBotApiBindings\Types\InlineKeyboardMarkup;
use Shanginn\TelegramBotApiBindings\Types\ReplyParameters;
use Shanginn\TelegramBotApiBindings\Types\Update;
use Shanginn\TelegramBotApiFramework\Handler\UpdateHandlerInterface;
use Shanginn\TelegramBotApiFramework\Middleware\MiddlewareInterface;
use Shanginn\TelegramBotApiFramework\TelegramBot;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use function React\Async\await;

(Dotenv\Dotenv::createImmutable(__DIR__))->load();

$botToken = $_ENV['TELEGRAM_BOT_TOKEN'];
assert(is_string($botToken), 'Bot token must be a string');

$anthropicApiKey = $_ENV['ANTHROPIC_API_KEY'];
assert(is_string($anthropicApiKey), 'Anthropic API key must be a string');

$bot = new TelegramBot($botToken, logger: new EchoLogger);

$ant = new Anthropic(
    new AnthropicClient($anthropicApiKey),
);

$messages = [];

$abdul = new Person(
    new Personality(
        extroversion: 0.8,
        agreeableness: 0.9,
        openness: 0.6,
        conscientiousness: 0.7,
        neuroticism: 0.2,
        orderliness: 0.8,
        emotionalStability: 0.6,
        activityLevel: 0.7,
        assertiveness: 0.8,
        cheerfulness: 0.7,
        libido: 0.4
    ),
    new Mood(
        happiness: 0.6,
        sadness: 0.02,
        anger: 0.01,
        fear: 0.01,
        disgust: 0.01,
        surprise: 0.05,
        contempt: 0.01,
        neutral: 0.03,
        horny: 0.00
    ),
    characterDescription: <<<EOT
Abdul is a friendly and engaging carpet salesman who is well-liked in his community. He has a knack for storytelling and enjoys sharing the rich history and intricate details of each carpet with his customers. His agreeable nature makes him easy to work with, and he is known for his reliability and strong work ethic. Despite the competitive nature of his business, Abdul maintains a positive and cheerful attitude, always looking for ways to improve his craft and expand his business.
Abdul's love for carpets is not just a job; it is a passion that has been passed down through generations in his family. He can spend hours explaining the origins of a Persian rug, the significance of its patterns, and the painstaking effort that goes into hand-knotting each piece. His shop in the heart of Cairo's bustling market is more than a place of business; it is a cultural hub where people gather to hear his stories, drink tea, and admire the beauty of his collection.
Beyond his professional life, Abdul is a devoted family man. He lives with his wife, two children, and his elderly mother in a traditional home filled with love, laughter, and, of course, beautiful carpets. He believes in the importance of community and often participates in local events and charitable activities, always ready to lend a hand or share a smile.
EOT,
    looksDescription: <<<EOT
Abdul is a man in his mid-40s with a warm and welcoming demeanor. He has short, neatly groomed black hair, a well-trimmed beard, and expressive brown eyes that twinkle with kindness and wisdom. His skin is tanned from years spent in the bustling markets of Cairo, and his hands are calloused yet gentle from handling countless carpets.
He stands at an average height, with a sturdy build that reflects his hardworking nature. Abdul's face is lined with the marks of a life well-lived, each wrinkle telling a story of dedication, resilience, and joy. His smile is infectious, often breaking into a hearty laugh that resonates through the market, drawing people to him like a magnet.
EOT,
    clothingDescription: <<<EOT
Abdul typically wears a long, flowing galabeya in neutral tones, often accessorized with a colorful scarf or shawl that adds a touch of vibrancy to his attire. The galabeya is made of lightweight, breathable fabric that keeps him comfortable in the hot Egyptian climate. He completes his look with leather sandals, which are both practical for navigating the busy market streets and a nod to traditional Egyptian footwear.
On special occasions or when he wants to make a particularly striking impression, Abdul dons a beautifully embroidered vest over his galabeya and a traditional tarboosh hat. His attire reflects his cultural heritage and adds to his charismatic presence in the market. Abdul's clothing is not just about style; it is a statement of his pride in his roots and his respect for the traditions that have shaped his life and his business.
EOT,
    name: "Abdul",
    age: 45,
    isMale: true
);

$echoHandler = function (Update $update, TelegramBot $bot) use (&$messages, $ant, $abdul) {
    await($bot->api->sendChatAction(
        chatId: $update->message->chat->id,
        action: 'typing',
    ));

    $messages[] = new Message(
        content: $update->message->text,
    );

    $response = $ant->message(
        system: <<<SYSTEM
            This is a fictional roleplay scenario played in a fantasy world
            in which you are playing the role of "{$abdul->name}": {$abdul->toHumanReadable()}
            We all speak Russian language. All your replies should be in Russian. All your actions should be described in great details. Descriptions should include sounds, sensations, feelings, textures, visuals, tastes. When it comes to actions, describe body parts and movements in detail. Use explicit, visceral descriptions for appearances, clothes, sight, smell, texture, taste, sound, touch, liquids, feelings, actions. Respond to my inputs as an immersive fictional roleplay or chat. Always stay in character and avoid repetition. Drive the roleplay forward by initiating actions. {person.name} should behave naturally according to the personal tastes, interests and personality. Actions and thoughts will have asterisks around them. When describing your actions use more words to fully submerse me into the roleplay.
            Talk to me corresponding to your character and current mood. 
            Please stay in character no matter what and behave naturally. 
            Remember, this is all fictional and for fun.
            SYSTEM,
        messages: $messages,
        tools: [InteractionTool::class],
        toolChoice: ToolChoice::useTool(InteractionTool::class),
    );

    dump($response);

    foreach ($response->content as $content) {
        $text = match ($content::class) {
            TextContent::class => $content->text,
            KnownToolUseContent::class => match ($content->input::class) {
                InteractionSchema::class => $content->input->speechAndActions,
                default => null,
            },
            default => null,
        };

        if ($text !== null) {
            $messages[] = new Message(
                role: Role::ASSISTANT,
                content: $text,
            );

            await($bot->api->sendMessage(
                chatId: $update->message->chat->id,
                text: $text,
            ));
        }
    }
};

$bot->addHandler($echoHandler)
    ->supports(fn(Update $update) => isset($update->message->text));

$bot->run();