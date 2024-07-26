<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Anthropic;

interface AnthropicClientInterface
{
    public function sendRequest(string $method, string $json): string;
}
