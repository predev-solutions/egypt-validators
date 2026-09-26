<?php

declare(strict_types=1);

namespace Predev\EgyptValidators;

/**
 * Egyptian mobile numbers: 01X followed by 8 digits, where 01X is 010, 011, 012 or 015.
 *
 * Accepts local (010…), international (+2010…, 002010…, 2010…) and bare (10…) forms,
 * with spaces, dashes, dots, parentheses and Arabic-Indic digits.
 */
final class Mobile
{
    /** Returns the local 11-digit form (01XXXXXXXXX), or null when the number is not a valid Egyptian mobile. */
    public static function normalize(?string $number): ?string
    {
        $digits = preg_replace('/[\s\-\.\(\)]/u', '', Digits::normalize((string) $number));

        if ($digits === null || ! preg_match('/^(?:\+20|0020|20)?0?(1[0125]\d{8})$/', $digits, $m)) {
            return null;
        }

        return '0'.$m[1];
    }

    public static function isValid(?string $number): bool
    {
        return self::normalize($number) !== null;
    }

    /** E.164 form, e.g. +201012345678. */
    public static function toE164(?string $number): ?string
    {
        $local = self::normalize($number);

        return $local === null ? null : '+20'.substr($local, 1);
    }

    /** The network the number was originally issued on. Numbers can be ported, so treat this as a hint. */
    public static function carrier(?string $number): ?Carrier
    {
        $local = self::normalize($number);

        return $local === null ? null : Carrier::from(substr($local, 0, 3));
    }
}
