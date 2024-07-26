<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Anthropic\Tool;

interface ToolInterface
{
    /**
     * @return class-string<ToolSchemaInterface>
     */
    public static function getSchemaClass(): string;

    public static function getName(): string;

    public static function getDescription(): string;
}
