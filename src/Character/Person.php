<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Character;

class Person
{
    public function __construct(
        public Personality $personality,
        public Mood $mood,
        public string $characterDescription,
        public string $looksDescription,
        public string $clothingDescription,
        public string $name,
        public int $age,
        public bool $isMale
    ) {}

    public function toHumanReadable(): string
    {
        $gender = $this->isMale ? 'guy' : 'girl';
        return <<<EOD
            My name is {$this->name}, I am {$this->age} years old. I am a {$gender}
            My personality: {$this->personality->toHumanReadable()}
            My character: {$this->characterDescription}
            My looks: {$this->looksDescription}
            Outfit: {$this->clothingDescription}
            EOD;
    }
}