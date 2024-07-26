<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Anthropic\Message;

use Crell\Serde\Attributes as Serde;

/**
 * Represents the response from an API for a message request.
 */
final class MessageResponse
{
    /**
     * @param string                                 $id           unique identifier for the response object
     * @param string                                 $type         Type of the object, defaults to "message". Currently, only "message" is supported.
     * @param string                                 $role         the conversational role of the generated message, defaults to "assistant"
     * @param array<ResponseMessageContentInterface> $content      The content generated by the model. It's an array of content blocks (e.g., text, tool use).
     * @param string                                 $model        the model that handled the request
     * @param string|null                            $stopReason   the reason why the model stopped generating content, such as "end_turn", "max_tokens", "stop_sequence", or "tool_use"
     * @param string|null                            $stopSequence specifies which custom stop sequence was generated, if any
     * @param array                                  $usage        information about billing and rate-limit usage based on token counts
     */
    public function __construct(
        public string $id,
        #[Serde\SequenceField(arrayType: ResponseMessageContentInterface::class)]
        public array $content,
        public string $model,
        public array $usage,
        public string $type = 'message',
        public string $role = 'assistant',
        public ?string $stopReason = null,
        public ?string $stopSequence = null,
    ) {}
}
