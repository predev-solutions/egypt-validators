<?php

declare(strict_types=1);

namespace Predev\EgyptValidators\Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Predev\EgyptValidators\Carrier;
use Predev\EgyptValidators\Gender;
use Predev\EgyptValidators\Laravel\EgyptianMobile;
use Predev\EgyptValidators\Laravel\EgyptianNationalId;

final class TranslatedMessagesTest extends TestCase
{
    private const LANG = __DIR__.'/../lang';

    /** @param array<string, mixed> $data @param array<string, mixed> $rules */
    private function validate(string $locale, array $data, array $rules): Validator
    {
        $loader = new FileLoader(new Filesystem(), []);
        $loader->addNamespace('egypt-validators', self::LANG);

        return new Validator(new Translator($loader, $locale), $data, $rules);
    }

    /** @return iterable<string, array{string, array<string, mixed>, array<string, mixed>, string}> */
    public static function messages(): iterable
    {
        $man = '29001010101234';
        $child = '32001010101234';

        yield 'en invalid id' => ['en', ['id' => '123'], ['id' => [new EgyptianNationalId()]], 'The id must be a valid Egyptian national ID.'];
        yield 'en min age' => ['en', ['id' => $child], ['id' => [EgyptianNationalId::make()->minAge(18)]], 'The id holder must be at least 18 years old.'];
        yield 'en female' => ['en', ['id' => $man], ['id' => [EgyptianNationalId::make()->gender(Gender::Female)]], 'The id must belong to a female.'];
        yield 'en invalid mobile' => ['en', ['phone' => '123'], ['phone' => [new EgyptianMobile()]], 'The phone must be a valid Egyptian mobile number.'];
        yield 'en carriers' => ['en', ['phone' => '01012345678'], ['phone' => [EgyptianMobile::make()->carriers(Carrier::Orange, Carrier::We)]], 'The phone must be on one of: Orange, WE.'];

        yield 'ar invalid id' => ['ar', ['id' => '123'], ['id' => [new EgyptianNationalId()]], 'يجب أن يكون id رقمًا قوميًا مصريًا صحيحًا.'];
        yield 'ar min age' => ['ar', ['id' => $child], ['id' => [EgyptianNationalId::make()->minAge(18)]], 'يجب ألا يقل عمر صاحب id عن 18 سنة.'];
        yield 'ar female' => ['ar', ['id' => $man], ['id' => [EgyptianNationalId::make()->gender(Gender::Female)]], 'يجب أن يكون id لأنثى.'];
        yield 'ar invalid mobile' => ['ar', ['phone' => '123'], ['phone' => [new EgyptianMobile()]], 'يجب أن يكون phone رقم موبايل مصريًا صحيحًا.'];
        yield 'ar carriers' => ['ar', ['phone' => '01012345678'], ['phone' => [EgyptianMobile::make()->carriers(Carrier::Orange, Carrier::We)]], 'يجب أن يكون phone على إحدى الشبكات التالية: أورنج, وي.'];
    }

    /** @param array<string, mixed> $data @param array<string, mixed> $rules */
    #[DataProvider('messages')]
    public function test_rule_messages_are_translated(string $locale, array $data, array $rules, string $expected): void
    {
        $v = $this->validate($locale, $data, $rules);

        $this->assertTrue($v->fails());
        $this->assertSame($expected, $v->errors()->first(array_key_first($data)));
    }

    public function test_unknown_locale_falls_back_to_english(): void
    {
        $v = $this->validate('fr', ['phone' => '123'], ['phone' => [new EgyptianMobile()]]);

        $this->assertSame('The phone must be a valid Egyptian mobile number.', $v->errors()->first('phone'));
    }

    public function test_translator_without_the_package_namespace_falls_back_to_english(): void
    {
        $v = new Validator(new Translator(new ArrayLoader(), 'ar'), ['id' => '29001010101234'], ['id' => [EgyptianNationalId::make()->gender(Gender::Female)]]);

        $this->assertSame('The id must belong to a female.', $v->errors()->first('id'));
    }

    public function test_app_can_override_a_message(): void
    {
        $loader = new ArrayLoader();
        $loader->addMessages('en', 'validation', ['mobile' => 'Bad number for :attribute.'], 'egypt-validators');

        $v = new Validator(new Translator($loader, 'en'), ['phone' => '123'], ['phone' => [new EgyptianMobile()]]);

        $this->assertSame('Bad number for phone.', $v->errors()->first('phone'));
    }

    public function test_arabic_and_english_files_have_the_same_keys(): void
    {
        $en = require self::LANG.'/en/validation.php';
        $ar = require self::LANG.'/ar/validation.php';

        $this->assertSame($this->keys($en), $this->keys($ar));
    }

    public function test_every_carrier_has_a_translated_name(): void
    {
        foreach (['en', 'ar'] as $locale) {
            $lines = require self::LANG."/{$locale}/validation.php";

            foreach (Carrier::cases() as $carrier) {
                $this->assertArrayHasKey(strtolower($carrier->name), $lines['carriers'], "{$locale}: {$carrier->name}");
            }
        }
    }

    /** @param array<string, mixed> $lines @return list<string> */
    private function keys(array $lines, string $prefix = ''): array
    {
        $keys = [];
        foreach ($lines as $key => $value) {
            $keys = is_array($value)
                ? [...$keys, ...$this->keys($value, "{$prefix}{$key}.")]
                : [...$keys, $prefix.$key];
        }
        sort($keys);

        return $keys;
    }
}
