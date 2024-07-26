<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman;

use Psr\Log\AbstractLogger;
use Stringable;

class EchoLogger extends AbstractLogger
{
    public function log($level, string|Stringable $message, array $context = []): void
    {
        echo "[{$level}] {$message} ";

        if (!empty($context)) {
            echo json_encode($context);
        }

        echo "\n";
    }
}
