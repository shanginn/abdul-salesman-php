<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Anthropic\Message;

use Crell\Serde\Attributes\ClassSettings;
use Crell\Serde\Renaming\Cases;
use Shanginn\AbdulSalesman\Anthropic\Tool\ToolInterface;

#[ClassSettings(renameWith: Cases::snake_case)]
final class MessageRequest
{
    /**
     * @param string                    $model         The model that will complete your prompt. Refer to the API's model documentation for additional details and options.
     * @param array<Message>            $messages      Array of input messages for conversational context. Each message is an associative array with 'role' and 'content'.
     * @param int                       $maxTokens     The maximum number of tokens to generate before stopping. This may not be reached if the model completes its response earlier.
     * @param array|null                $metadata      optional metadata about the request
     * @param array|null                $stopSequences custom text sequences that will trigger a stop in generation
     * @param bool|null                 $stream        whether to stream the response incrementally
     * @param string|null               $system        system prompt for specific instructions to the model
     * @param float|null                $temperature   Controls the randomness of the response. A lower value makes the response more deterministic.
     * @param ToolChoice|null           $toolChoice    specifies how the model should use the provided tools
     * @param array<ToolInterface>|null $tools         definitions and descriptions of tools that the model may use during the response generation
     */
    public function __construct(
        public string $model,
        public array $messages,
        public ?string $system = null,
        public ?float $temperature = 0.0,
        public int $maxTokens = 1024,
        public ?array $metadata = null,
        public ?array $stopSequences = null,
        public bool $stream = false,
        public ?ToolChoice $toolChoice = null,
        public ?array $tools = null,
    ) {}
}
