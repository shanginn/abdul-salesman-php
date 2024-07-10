<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Anthropic\Message;

enum ToolChoiceType: string
{
    /** Allows Claude to decide whether to call any provided tools or not. This is the default value. */
    case AUTO = 'auto';

    /** Forces Claude to call one of the provided tools. */
    case ANY = 'any';

    /** Forces Claude to call a particular tool. */
    case TOOL = 'tool';
}