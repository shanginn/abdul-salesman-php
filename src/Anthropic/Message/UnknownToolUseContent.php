<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Anthropic\Message;

final class UnknownToolUseContent implements ResponseMessageContentInterface
{
    /**
     * @param string $id    identifier for the specific tool use instance
     * @param array  $input the input data provided to the tool
     * @param string $name  the name of the tool used
     */
    public function __construct(
        public string $id,
        public array $input,
        public string $name,
    ) {}
}
