<?php

declare(strict_types=1);

namespace Predev\EgyptValidators\Tests;

use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Validator;
use PHPUnit\Framework\TestCase;
use Predev\EgyptValidators\Carrier;
use Predev\EgyptValidators\Gender;
use Predev\EgyptValidators\Laravel\EgyptianMobile;
use Predev\EgyptValidators\Laravel\EgyptianNationalId;

final class LaravelRulesTest extends TestCase
{
    /** @param array<string, mixed> $data @param array<string, mixed> $rules */
    private function validate(array $data, array $rules): Validator
    {
        return new Validator(new Translator(new ArrayLoader(), 'en'), $data, $rules);
    }

    public function test_national_id_rule(): void
    {
        $this->assertTrue($this->validate(['id' => '29001010101234'], ['id' => [new EgyptianNationalId()]])->passes());

        $v = $this->validate(['id' => '123'], ['id' => [new EgyptianNationalId()]]);
        $this->assertTrue($v->fails());
        $this->assertSame('The id must be a valid Egyptian national ID.', $v->errors()->first('id'));
    }

    public function test_national_id_rule_min_age_and_gender(): void
    {
        $adultMale = '29001010101234';
        $this->assertTrue($this->validate(['id' => $adultMale], ['id' => [EgyptianNationalId::make()->minAge(18)->gender(Gender::Male)]])->passes());
        $this->assertTrue($this->validate(['id' => $adultMale], ['id' => [EgyptianNationalId::make()->gender(Gender::Female)]])->fails());

        $child = '32001010101234';
        $this->assertTrue($this->validate(['id' => $child], ['id' => [EgyptianNationalId::make()->minAge(18)]])->fails());
    }

    public function test_mobile_rule(): void
    {
        $this->assertTrue($this->validate(['phone' => '+20 101 234 5678'], ['phone' => [new EgyptianMobile()]])->passes());
        $this->assertTrue($this->validate(['phone' => ['array']], ['phone' => [new EgyptianMobile()]])->fails());

        $v = $this->validate(['phone' => '01012345678'], ['phone' => [EgyptianMobile::make()->carriers(Carrier::Orange, Carrier::We)]]);
        $this->assertTrue($v->fails());
        $this->assertSame('The phone must be on one of: Orange, WE.', $v->errors()->first('phone'));
    }
}
