<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Anthropic\Message;

use Crell\Serde\Attributes\StaticTypeMap;
#[StaticTypeMap(key: 'type', map: [
    'text' => TextContent::class,
    'tool_use' => UnknownToolUseContent::class,
])]
interface ResponseMessageContentInterface
{

}