<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Anthropic\Tool;

interface ToolSchemaInterface
{
    /**
     * @return class-string<ToolInterface>
     */
    public static function getTool(): string;
}