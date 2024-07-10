<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Anthropic\Message;

enum Role: string {
    case SYSTEM = 'system';
    case USER = 'user';
    case ASSISTANT = 'assistant';
}