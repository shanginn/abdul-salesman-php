<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Character;

use Shanginn\AbdulSalesman\Anthropic\Tool\AbstractTool;

class InteractionTool extends AbstractTool
{
    protected static string $name        = 'interaction';
    protected static string $description = <<<'EOT'
        A place of reflection and self-discovery. I use this to think about things and change my mood,
        for interaction with people and figuring out what to do and say.
        
        THE MOST IMPORTANT PART TO FILL IS `Speech and Actions`. PLEASE FIND SOMETHING TO TELL. it's very very important
        EOT;
    protected static string $schema = InteractionSchema::class;
}
