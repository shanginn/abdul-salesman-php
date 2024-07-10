<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Tarot;

use Shanginn\AbdulSalesman\Anthropic\Tool\ToolSchemaInterface;
use Spiral\JsonSchemaGenerator\Attribute\Field;

class DoTarotReadingSchema implements ToolSchemaInterface
{
    public function __construct(
        #[Field(
            title: 'Question',
            description: 'The full question to answer. More details are better. The question must be as close to the original question as possible.'
        )]
        public readonly string $question,

        #[Field(
            title: 'Context',
            description: 'The context of the question. This is additional information provided by the user that can be used to answer the question. Only use facts from the conversation.'
        )]
        public readonly ?string $context = null,

        #[Field(
            title: 'Image Context',
            description: 'Information from the image in the dialog the question. Please include it here if it\'s relevant to the question. It is very important to keep image context, and make short and to the point. REQUIRED: If the question is about the image.'
        )]
        public readonly ?string $imageContext = null,
    ) {
    }
}