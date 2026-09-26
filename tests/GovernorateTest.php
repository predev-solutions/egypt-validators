<?php

declare(strict_types=1);

namespace Predev\EgyptValidators\Tests;

use PHPUnit\Framework\TestCase;
use Predev\EgyptValidators\Governorate;

final class GovernorateTest extends TestCase
{
    public function test_lists_all_27_governorates(): void
    {
        $this->assertCount(27, Governorate::governorates());
        $this->assertNotContains(Governorate::BornAbroad, Governorate::governorates());
    }

    public function test_every_code_has_arabic_and_english_names(): void
    {
        foreach (Governorate::cases() as $governorate) {
            $this->assertMatchesRegularExpression('/^\d{2}$/', $governorate->code());
            $this->assertNotSame('', $governorate->nameEn());
            $this->assertMatchesRegularExpression('/\p{Arabic}/u', $governorate->nameAr());
        }
    }

    public function test_looks_up_by_code(): void
    {
        $this->assertSame(Governorate::Alexandria, Governorate::from('02'));
        $this->assertSame('الإسكندرية', Governorate::from('02')->nameAr());
        $this->assertNull(Governorate::tryFrom('05'));
    }
}
