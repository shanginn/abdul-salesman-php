<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;

#[Entity]
class Message
{
    #[Column(type: "primary")]
    public int $id;

    public function __construct(
        #[Column(type: "text")]
        public string $text,

        #[Column(type: "text")]
        public string $chatId,

        #[Column(type: "datetime")]
        public \DateTimeImmutable $createdAt,

        #[Column(type: "text")]
        public string $fromUserId,

        #[Column(type: "text", nullable: true)]
        public string $fromUsername,

        #[Column(type: "boolean", typecast: "bool")]
        public bool $isFinishMessage = false
    ) {
    }
}