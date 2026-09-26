<?php

declare(strict_types=1);

namespace Predev\EgyptValidators\Tests;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Predev\EgyptValidators\Gender;
use Predev\EgyptValidators\Governorate;
use Predev\EgyptValidators\NationalId;

final class NationalIdTest extends TestCase
{
    private DateTimeImmutable $today;

    protected function setUp(): void
    {
        $this->today = new DateTimeImmutable('2026-09-26');
    }

    public function test_parses_a_1900s_male_born_in_cairo(): void
    {
        $info = NationalId::parse('29001010101234', $this->today);

        $this->assertNotNull($info);
        $this->assertSame('1990-01-01', $info->birthDate->format('Y-m-d'));
        $this->assertSame(Governorate::Cairo, $info->governorate);
        $this->assertSame(Gender::Male, $info->gender);
        $this->assertSame('0123', $info->serial);
        $this->assertSame(36, $info->age($this->today));
    }

    public function test_parses_a_2000s_female_born_in_giza(): void
    {
        $info = NationalId::parse('30502282101248', $this->today);

        $this->assertNotNull($info);
        $this->assertSame('2005-02-28', $info->birthDate->format('Y-m-d'));
        $this->assertSame(Governorate::Giza, $info->governorate);
        $this->assertSame(Gender::Female, $info->gender);
    }

    public function test_accepts_arabic_indic_digits_and_surrounding_spaces(): void
    {
        $this->assertTrue(NationalId::isValid(' ٢٩٠٠١٠١٠١٠١٢٣٤ ', $this->today));
    }

    public function test_accepts_the_born_abroad_code(): void
    {
        $this->assertSame(Governorate::BornAbroad, NationalId::parse('29512318801231', $this->today)?->governorate);
    }

    public function test_accepts_a_leap_day(): void
    {
        $this->assertTrue(NationalId::isValid('30002290101231', $this->today));
    }

    /** @return array<string, array{?string}> */
    public static function invalidNumbers(): array
    {
        return [
            'null' => [null],
            'empty' => [''],
            'too short' => ['2900101010123'],
            'too long' => ['290010101012345'],
            'letters' => ['2900101010123A'],
            'century 1' => ['19001010101234'],
            'century 4' => ['49001010101234'],
            'month 13' => ['29013010101234'],
            'day 32' => ['29001320101234'],
            'feb 30' => ['29002300101234'],
            'not a leap year' => ['29002290101234'],
            'unknown governorate 05' => ['29001010501234'],
            'unknown governorate 99' => ['29001019901234'],
            'born in the future' => ['32701010101234'],
        ];
    }

    #[DataProvider('invalidNumbers')]
    public function test_rejects_invalid_numbers(?string $number): void
    {
        $this->assertNull(NationalId::parse($number, $this->today));
        $this->assertFalse(NationalId::isValid($number, $this->today));
    }
}
