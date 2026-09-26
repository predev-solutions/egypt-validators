<?php

declare(strict_types=1);

namespace Predev\EgyptValidators;

use DateTimeImmutable;

/**
 * Egyptian national ID: 14 digits laid out as C YYMMDD GG SSSS X.
 *
 * C    century of birth (2 = 1900s, 3 = 2000s)
 * YYMMDD  date of birth
 * GG   governorate of birth
 * SSSS serial; its last digit is odd for males, even for females
 * X    check digit
 */
final class NationalId
{
    public static function isValid(?string $number, ?DateTimeImmutable $today = null): bool
    {
        return self::parse($number, $today) !== null;
    }

    public static function parse(?string $number, ?DateTimeImmutable $today = null): ?NationalIdInfo
    {
        $number = Digits::normalize((string) $number);

        if (! preg_match('/^([23])(\d{2})(\d{2})(\d{2})(\d{2})(\d{4})\d$/', $number, $m)) {
            return null;
        }

        [, $century, $yy, $mm, $dd, $gov, $serial] = $m;
        $year = ($century === '2' ? 1900 : 2000) + (int) $yy;

        if (! checkdate((int) $mm, (int) $dd, $year)) {
            return null;
        }

        $birthDate = new DateTimeImmutable(sprintf('%04d-%s-%s', $year, $mm, $dd));
        if ($birthDate > ($today ?? new DateTimeImmutable('today'))) {
            return null;
        }

        $governorate = Governorate::tryFrom($gov);
        if ($governorate === null) {
            return null;
        }

        $gender = ((int) $serial[3]) % 2 === 1 ? Gender::Male : Gender::Female;

        return new NationalIdInfo($number, $birthDate, $governorate, $gender, $serial);
    }
}
