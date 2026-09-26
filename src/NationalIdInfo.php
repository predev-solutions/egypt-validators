<?php

declare(strict_types=1);

namespace Predev\EgyptValidators;

use DateTimeImmutable;

final readonly class NationalIdInfo
{
    public function __construct(
        public string $number,
        public DateTimeImmutable $birthDate,
        public Governorate $governorate,
        public Gender $gender,
        public string $serial,
    ) {
    }

    public function age(?DateTimeImmutable $on = null): int
    {
        return $this->birthDate->diff($on ?? new DateTimeImmutable('today'))->y;
    }
}
