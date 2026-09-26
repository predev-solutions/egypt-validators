<?php

declare(strict_types=1);

namespace Predev\EgyptValidators\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Predev\EgyptValidators\Carrier;
use Predev\EgyptValidators\Mobile;

final class MobileTest extends TestCase
{
    /** @return array<string, array{string, string}> */
    public static function validNumbers(): array
    {
        return [
            'local' => ['01012345678', '01012345678'],
            'plus 20' => ['+201112345678', '01112345678'],
            '0020' => ['00201212345678', '01212345678'],
            '20 without plus' => ['201512345678', '01512345678'],
            'bare' => ['1012345678', '01012345678'],
            'plus 20 with extra 0' => ['+2001012345678', '01012345678'],
            'spaces' => ['010 1234 5678', '01012345678'],
            'dashes and parens' => ['(+20) 10-1234-5678', '01012345678'],
            'arabic digits' => ['٠١٠١٢٣٤٥٦٧٨', '01012345678'],
        ];
    }

    #[DataProvider('validNumbers')]
    public function test_normalizes_valid_numbers(string $input, string $expected): void
    {
        $this->assertSame($expected, Mobile::normalize($input));
        $this->assertTrue(Mobile::isValid($input));
    }

    /** @return array<string, array{?string}> */
    public static function invalidNumbers(): array
    {
        return [
            'null' => [null],
            'empty' => [''],
            'landline' => ['0223456789'],
            'unknown prefix 013' => ['01312345678'],
            'unknown prefix 014' => ['01412345678'],
            'too short' => ['0101234567'],
            'too long' => ['010123456789'],
            'letters' => ['0101234567a'],
            'other country' => ['+971501234567'],
        ];
    }

    #[DataProvider('invalidNumbers')]
    public function test_rejects_invalid_numbers(?string $input): void
    {
        $this->assertNull(Mobile::normalize($input));
        $this->assertFalse(Mobile::isValid($input));
        $this->assertNull(Mobile::carrier($input));
        $this->assertNull(Mobile::toE164($input));
    }

    public function test_formats_as_e164(): void
    {
        $this->assertSame('+201012345678', Mobile::toE164('010 1234 5678'));
    }

    public function test_detects_the_carrier(): void
    {
        $this->assertSame(Carrier::Vodafone, Mobile::carrier('01012345678'));
        $this->assertSame(Carrier::Etisalat, Mobile::carrier('+201112345678'));
        $this->assertSame(Carrier::Orange, Mobile::carrier('01212345678'));
        $this->assertSame(Carrier::We, Mobile::carrier('01512345678'));
    }
}
