<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Anthropic\Tool;

use Spiral\JsonSchemaGenerator\Generator;
use Spiral\JsonSchemaGenerator\GeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ToolNormalizer implements NormalizerInterface
{
    private GeneratorInterface $jsonSchemaGenerator;

    public function __construct()
    {
        $this->jsonSchemaGenerator = new Generator();
    }

    public function normalize(mixed $object, ?string $format = null, array $context = [])
    {
        assert(is_a($object, ToolInterface::class, true));

        return [
            'name'         => $object::getName(),
            'description'  => $object::getDescription(),
            'input_schema' => [
                'type' => 'object',
                ...$this->jsonSchemaGenerator->generate($object::getSchemaClass())->jsonSerialize(),
            ],
        ];
    }

    public function supportsNormalization(mixed $data, ?string $format = null)
    {
        return is_a($data, ToolInterface::class, true);
    }
}
