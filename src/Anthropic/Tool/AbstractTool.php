<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Anthropic\Tool;

abstract class AbstractTool implements ToolInterface
{
    protected static string $name;
    protected static string $description;
    protected static string $schema;

    public static function getSchemaClass(): string
    {
        return static::$schema;
    }

    public static function getName(): string
    {
        return static::$name;
    }

    public static function getDescription(): string
    {
        return static::$description;
    }
}
