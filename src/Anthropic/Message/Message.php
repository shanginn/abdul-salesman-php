<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Anthropic\Message;

class Message {
    public function __construct(
        public string $content,
        public Role $role = Role::USER,
        public ?array $meta = null
    ) {
    }
}