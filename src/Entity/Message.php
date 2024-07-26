<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;

#[Entity]
final readonly class Message
{
    #[Column(type: "primary")]
    public int $id;

    public function __construct(
        #[Column(type: "text")]
        public string $text,

        #[Column(type: "text")]
        public string $chatId,

        #[Column(type: "datetime")]
        public \DateTime $createdAt,

        #[Column(type: "text")]
        public string $fromUserId,

        #[Column(type: "text", nullable: true)]
        public string $fromUsername,
    ) {
    }
}