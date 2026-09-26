<?php

declare(strict_types=1);

namespace Predev\EgyptValidators;

enum Carrier: string
{
    case Vodafone = '010';
    case Etisalat = '011';
    case Orange = '012';
    case We = '015';

    public function label(): string
    {
        return match ($this) {
            self::Vodafone => 'Vodafone',
            self::Etisalat => 'e& (Etisalat)',
            self::Orange => 'Orange',
            self::We => 'WE',
        };
    }
}
