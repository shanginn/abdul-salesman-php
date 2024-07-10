<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Tarot;

use Shanginn\AbdulSalesman\Anthropic\Tool\AbstractTool;

class DoTarotReadingAbstractTool extends AbstractTool
{
    public function __construct()
    {
        parent::__construct(
            name: 'do_tarot_reading',
            description: 'If there is a question, use this function to answer it with a tarot reading',
            schema: DoTarotReadingSchema::class
        );
    }
}