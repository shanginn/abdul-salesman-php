<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Anthropic\Message;

use Shanginn\AbdulSalesman\Anthropic\Tool\ToolInterface;

final readonly class ToolChoice
{
    /**
     * @param ToolChoiceType $type
     * @param class-string<ToolInterface>|null $tool
     */
    public function __construct(
        public ToolChoiceType $type,
        public ?string $tool = null,
    )
    {
        if ($type === ToolChoiceType::TOOL) {
            if ($tool === null) {
                throw new \InvalidArgumentException('Tool must be provided when type is TOOL.');
            }

            if(!is_a($tool, ToolInterface::class, true)) {
                throw new \InvalidArgumentException('Tool must implement ToolInterface.');
            }
        }
    }

    /**
     * @param class-string<ToolInterface> $tool
     * @return self
     */
    public static function useTool(string $tool): self
    {
        if(!is_a($tool, ToolInterface::class, true)) {
            throw new \InvalidArgumentException('Tool is not a ToolInterface');
        }

        return new self(ToolChoiceType::TOOL, $tool);
    }
}