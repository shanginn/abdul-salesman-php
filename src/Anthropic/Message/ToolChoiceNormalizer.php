<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Anthropic\Message;

use Shanginn\AbdulSalesman\Anthropic\Tool\ToolInterface;
use Spiral\JsonSchemaGenerator\Generator;
use Spiral\JsonSchemaGenerator\GeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ToolChoiceNormalizer implements NormalizerInterface
{
    public function normalize(mixed $object, ?string $format = null, array $context = [])
    {
        assert($object instanceof ToolChoice);

        $choice = [
            'type' => $object->type->value,
        ];

        if ($object->type === ToolChoiceType::TOOL) {
            assert(is_a($object->tool, ToolInterface::class, true));

            $choice['name'] = $object->tool::getName();
        }

        return $choice;
    }

    public function supportsNormalization(mixed $data, ?string $format = null)
    {
        return $data instanceof ToolChoice;
    }
}