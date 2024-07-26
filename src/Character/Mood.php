<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Character;

class Mood
{
    public function __construct(
        public float $happiness,
        public float $sadness,
        public float $anger,
        public float $fear,
        public float $disgust,
        public float $surprise,
        public float $contempt,
        public float $neutral,
        public float $horny
    ) {}

    public function toHumanReadable(): string
    {
        return <<<EOD
            happiness={$this->happiness},
            sadness={$this->sadness},
            anger={$this->anger},
            fear={$this->fear},
            disgust={$this->disgust},
            surprise={$this->surprise},
            contempt={$this->contempt},
            neutral={$this->neutral},
            horny={$this->horny}
            EOD;
    }
}
