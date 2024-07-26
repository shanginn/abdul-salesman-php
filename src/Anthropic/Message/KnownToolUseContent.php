<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Anthropic\Message;

use Shanginn\AbdulSalesman\Anthropic\Tool\ToolSchemaInterface;
use Shanginn\AbdulSalesman\Character\InteractionTool;

final class KnownToolUseContent implements ResponseMessageContentInterface
{
    /**
     * @param string                        $id    identifier for the specific tool use instance
     * @param ToolSchemaInterface           $input the input data provided to the tool
     * @param class-string<InteractionTool> $tool  the name of the tool used
     */
    public function __construct(
        public string $id,
        public ToolSchemaInterface $input,
        public string $tool,
    ) {}
}
