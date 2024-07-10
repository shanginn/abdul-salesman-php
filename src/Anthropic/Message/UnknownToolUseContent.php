<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Anthropic\Message;

use Shanginn\AbdulSalesman\Anthropic\Tool\ToolSchemaInterface;
use Shanginn\AbdulSalesman\Character\InteractionTool;

final class UnknownToolUseContent implements ResponseMessageContentInterface
{
    /**
     * @param string $id Identifier for the specific tool use instance.
     * @param array $input The input data provided to the tool.
     * @param string $name The name of the tool used.
     */
    public function __construct(
        public string $id,
        public array $input,
        public string $name,
    ) {}
}