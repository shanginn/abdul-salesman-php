<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Anthropic;

use Crell\Serde\SerdeCommon;
use Shanginn\AbdulSalesman\Anthropic\Message\KnownToolUseContent;
use Shanginn\AbdulSalesman\Anthropic\Message\Message;
use Shanginn\AbdulSalesman\Anthropic\Message\MessageRequest;
use Shanginn\AbdulSalesman\Anthropic\Message\MessageResponse;
use Shanginn\AbdulSalesman\Anthropic\Message\ToolChoice;
use Shanginn\AbdulSalesman\Anthropic\Message\ToolChoiceNormalizer;
use Shanginn\AbdulSalesman\Anthropic\Message\UnknownToolUseContent;
use Shanginn\AbdulSalesman\Anthropic\Tool\ToolInterface;
use Shanginn\AbdulSalesman\Anthropic\Tool\ToolNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class Anthropic
{
    public SerializerInterface $serializer;
    private SerdeCommon $deserializer;

    public function __construct(
        private AnthropicClientInterface $client,
        private string $model = 'claude-3-5-sonnet-20240620',
    )
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [
            new BackedEnumNormalizer(),
            new ToolNormalizer(),
            new ToolChoiceNormalizer(),
            new ObjectNormalizer(
                null,
                new CamelCaseToSnakeCaseNameConverter()
            ),
        ];

        $this->serializer = new Serializer($normalizers, $encoders);
        $this->deserializer = new SerdeCommon();
    }

    /**
     * Sends a message to the model and retrieves the response.
     *
     * @param array<Message> $messages Array of input messages. Each message should be an associative array
     *                        with 'role' and 'content' keys.
     * @param int $maxTokens The maximum number of tokens to generate before stopping.
     * @param array $stopSequences Optional array of custom text sequences that will cause the model
     *                             to stop generating.
     * @param float $temperature Amount of randomness injected into the response. Range: 0.0 to 1.0.
     * @param bool $stream Whether to incrementally stream the response using server-sent events.
     * @param ToolChoice|null $toolChoice Specifies how the model should use the provided tools.
     * @param array<class-string<ToolInterface>>|null $tools Definitions and descriptions of tools that the model may use during the response generation.
     *
     * @return MessageResponse The model's response.
     */
    public function message(
        array $messages,
        ?string $system = null,
        ?float $temperature = 0.0,
        int $maxTokens = 1024,
        ?array $metadata = null,
        ?array $stopSequences = null,
        bool $stream = false,
        ?ToolChoice $toolChoice = null,
        ?array $tools = null,
    ): MessageResponse {
        $body = $this->serializer->serialize(new MessageRequest(
            model: $this->model,
            messages: $messages,
            system: $system,
            maxTokens: $maxTokens,
            metadata: $metadata,
            stopSequences: $stopSequences,
            temperature: $temperature,
            stream: $stream,
            toolChoice: $toolChoice,
            tools: $tools,
        ), 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);

        $responseJson = $this->client->sendRequest( 'messages', $body);

        $response = $this->deserializer->deserialize($responseJson, 'json', MessageResponse::class);

        foreach ($response->content as $i => $content) {
            if ($content instanceof UnknownToolUseContent) {
                foreach ($tools as $tool) {
                    if ($tool::getName() === $content->name) {
                        try {
                            $toolInput = $this->deserializer->deserialize(
                                serialized: $content->input,
                                from: 'array',
                                to: $tool::getSchemaClass(),
                            );
                        } catch (\Throwable $e) {
                            break;
                        }

                        $response->content[$i] = new KnownToolUseContent(
                            id: $content->id,
                            input: $toolInput,
                            tool: $tool,
                        );
                    }
                }
            }
        }

        return $response;
    }
}