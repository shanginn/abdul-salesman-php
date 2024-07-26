<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Character;

class Personality
{
    public function __construct(
        public float $extroversion,
        public float $agreeableness,
        public float $openness,
        public float $conscientiousness,
        public float $neuroticism,
        public float $orderliness,
        public float $emotionalStability,
        public float $activityLevel,
        public float $assertiveness,
        public float $cheerfulness,
        public float $greed,
        public float $libido
    ) {}

    public function toHumanReadable(): string
    {
        return <<<EOD
            extroversion={$this->extroversion},
            agreeableness={$this->agreeableness},
            openness={$this->openness},
            conscientiousness={$this->conscientiousness},
            neuroticism={$this->neuroticism},
            orderliness={$this->orderliness},
            emotionalStability={$this->emotionalStability},
            activityLevel={$this->activityLevel},
            assertiveness={$this->assertiveness},
            cheerfulness={$this->cheerfulness},
            greed={$this->greed},
            libido={$this->libido},
            EOD;
    }
}
