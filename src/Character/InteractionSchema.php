<?php

declare(strict_types=1);

namespace Shanginn\AbdulSalesman\Character;

use Shanginn\AbdulSalesman\Anthropic\Tool\ToolSchemaInterface;
use Spiral\JsonSchemaGenerator\Attribute\Field;

class InteractionSchema implements ToolSchemaInterface
{
    public function __construct(
        #[Field(
            title: 'Internal Monologue',
            description: <<<'EOT'
                Self Reflection. Thoughts put into the mood change, this is the internal 
                monologue, it's not visible to anyone. I can think freely here and write 
                down my TRUE thoughts. Think in English about everything that happens in 
                the interactions and how it affects my mood. First reflect on the previous 
                mood, then think about what happened in the interaction and how it affected 
                my mood. Be consistent with the mood and the character. Think about my 
                personality and how they would react to the situation.
                EOT
        )]
        #[\Crell\Serde\Attributes\Field(requireValue: true)]
        public string $internalMonologue,
        #[Field(
            title: 'Happiness',
            description: 'My new happiness level from 0.0 to 1.0'
        )]
        #[\Crell\Serde\Attributes\Field(requireValue: true)]
        public float $happiness,
        #[Field(
            title: 'Sadness',
            description: 'My new sadness level from 0.0 to 1.0'
        )]
        #[\Crell\Serde\Attributes\Field(requireValue: true)]
        public float $sadness,
        #[Field(
            title: 'Anger',
            description: 'My new anger level from 0.0 to 1.0'
        )]
        #[\Crell\Serde\Attributes\Field(requireValue: true)]
        public float $anger,
        #[Field(
            title: 'Fear',
            description: 'My new fear level from 0.0 to 1.0'
        )]
        #[\Crell\Serde\Attributes\Field(requireValue: true)]
        public float $fear,
        #[Field(
            title: 'Disgust',
            description: 'My new disgust level from 0.0 to 1.0'
        )]
        #[\Crell\Serde\Attributes\Field(requireValue: true)]
        public float $disgust,
        #[Field(
            title: 'Surprise',
            description: 'My new surprise level from 0.0 to 1.0'
        )]
        #[\Crell\Serde\Attributes\Field(requireValue: true)]
        public float $surprise,
        #[Field(
            title: 'Contempt',
            description: 'My new contempt level from 0.0 to 1.0'
        )]
        #[\Crell\Serde\Attributes\Field(requireValue: true)]
        public float $contempt,
        #[Field(
            title: 'Neutral',
            description: 'My new neutral level from 0.0 to 1.0'
        )]
        #[\Crell\Serde\Attributes\Field(requireValue: true)]
        public float $neutral,
        #[Field(
            title: 'Horny',
            description: 'My new horny level from 0.0 to 1.0'
        )]
        #[\Crell\Serde\Attributes\Field(requireValue: true)]
        public float $horny,
        #[Field(
            title: 'Desire to Leave',
            description: 'My desire to leave this conversation from 0.0 to 1.0'
        )]
        #[\Crell\Serde\Attributes\Field(requireValue: true)]
        public float $desireToLeave,
        #[Field(
            title: 'Price is agreed and I am willing to sell the Gem of the Desert',
            description: <<<'TXT'
                This should only be true if the price is agreed and
                I'm willing to sell the 'Gem of the Desert' carpet.
                
                When this is true, this mean the game is over and the
                interaction is finished.
                TXT
        )]
        #[\Crell\Serde\Attributes\Field(requireValue: true)]
        public bool $priceIsAgreed,
        #[Field(
            title: 'Speech and Actions',
            description: <<<'EOT'
                The actions and speech that I do and say in this interaction. This is the 
                output that I will say and do out loud, in the world. I can only say things 
                here, no thoughts or internal monologue. This is the speech and actions 
                that the other characters will see and hear. Be consistent with the mood.
                
                This is a required parameter and must be filled in at any cost.
                EOT
        )]
        #[\Crell\Serde\Attributes\Field(requireValue: true)]
        public string $speechAndActions
    ) {}

    public static function getTool(): string
    {
        return InteractionTool::class;
    }
}
