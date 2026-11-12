<?php

declare(strict_types=1);

namespace Predev\EgyptValidators\Tests;

use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Predev\EgyptValidators\Laravel\EgyptValidatorsServiceProvider;

final class ServiceProviderTest extends TestCase
{
    private Container $app;

    /** @var list<string> */
    private array $tempDirs = [];

    protected function setUp(): void
    {
        $this->bootApp();
    }

    protected function tearDown(): void
    {
        foreach ($this->tempDirs as $dir) {
            (new Filesystem())->deleteDirectory($dir);
        }
    }

    /** @param list<string> $appLangPaths */
    private function bootApp(string $locale = 'en', array $appLangPaths = []): void
    {
        $app = new Container();
        $app->singleton('translator', fn () => new Translator(new FileLoader(new Filesystem(), $appLangPaths), $locale));
        $app->singleton('validator', fn (Container $app) => new Factory($app->make('translator'), $app));

        (new EgyptValidatorsServiceProvider($app))->boot();

        $this->app = $app;
    }

    /** @param array<string, mixed> $data @param array<string, mixed> $rules @param array<string, string> $messages */
    private function validate(array $data, array $rules, array $messages = []): \Illuminate\Validation\Validator
    {
        return $this->app->make('validator')->make($data, $rules, $messages);
    }

    /** @return iterable<string, array{string, string, bool}> */
    public static function values(): iterable
    {
        yield 'mobile valid' => ['egyptian_mobile', '+20 101 234 5678', true];
        yield 'mobile arabic digits' => ['egyptian_mobile', '٠١٠١٢٣٤٥٦٧٨', true];
        yield 'mobile with dashes' => ['egyptian_mobile', '010-1234-5678', true];
        yield 'mobile wrong prefix' => ['egyptian_mobile', '01312345678', false];
        yield 'mobile too short' => ['egyptian_mobile', '0101234', false];
        yield 'national id valid' => ['egyptian_national_id', '29001010101234', true];
        yield 'national id bad date' => ['egyptian_national_id', '29002300101234', false];
        yield 'national id too short' => ['egyptian_national_id', '123', false];
    }

    #[DataProvider('values')]
    public function test_string_rules(string $rule, string $value, bool $passes): void
    {
        $this->assertSame($passes, $this->validate(['field' => $value], ['field' => "required|{$rule}"])->passes());
    }

    public function test_non_string_values_fail(): void
    {
        $this->assertTrue($this->validate(['phone' => ['01012345678']], ['phone' => 'egyptian_mobile'])->fails());
    }

    public function test_empty_optional_value_is_skipped_like_other_rules(): void
    {
        $this->assertTrue($this->validate(['phone' => ''], ['phone' => 'nullable|egyptian_mobile'])->passes());
        $this->assertTrue($this->validate(['phone' => ''], ['phone' => 'required|egyptian_mobile'])->fails());
    }

    public function test_english_messages(): void
    {
        $v = $this->validate(['phone' => '123', 'id' => '123'], ['phone' => 'egyptian_mobile', 'id' => 'egyptian_national_id']);

        $this->assertSame('The phone must be a valid Egyptian mobile number.', $v->errors()->first('phone'));
        $this->assertSame('The id must be a valid Egyptian national ID.', $v->errors()->first('id'));
    }

    public function test_arabic_messages(): void
    {
        $this->bootApp('ar');

        $v = $this->validate(['phone' => '123', 'id' => '123'], ['phone' => 'egyptian_mobile', 'id' => 'egyptian_national_id']);

        $this->assertSame('يجب أن يكون phone رقم موبايل مصريًا صحيحًا.', $v->errors()->first('phone'));
        $this->assertSame('يجب أن يكون id رقمًا قوميًا مصريًا صحيحًا.', $v->errors()->first('id'));
    }

    public function test_custom_attribute_names_are_used(): void
    {
        $v = $this->app->make('validator')->make(['phone' => '123'], ['phone' => 'egyptian_mobile'], [], ['phone' => 'mobile number']);

        $this->assertSame('The mobile number must be a valid Egyptian mobile number.', $v->errors()->first('phone'));
    }

    public function test_nested_attributes(): void
    {
        $v = $this->validate(['contacts' => [['phone' => '01012345678'], ['phone' => '123']]], ['contacts.*.phone' => 'egyptian_mobile']);

        $this->assertFalse($v->errors()->has('contacts.0.phone'));
        $this->assertSame('The contacts.1.phone must be a valid Egyptian mobile number.', $v->errors()->first('contacts.1.phone'));
    }

    public function test_custom_message_wins(): void
    {
        $v = $this->validate(['phone' => '123'], ['phone' => 'egyptian_mobile'], ['phone.egyptian_mobile' => 'Check :attribute.']);

        $this->assertSame('Check phone.', $v->errors()->first('phone'));
    }

    public function test_app_validation_file_message_wins(): void
    {
        $lang = sys_get_temp_dir().'/egypt-validators-'.bin2hex(random_bytes(4));
        $this->tempDirs[] = $lang;
        mkdir($lang.'/en', 0777, true);
        file_put_contents($lang.'/en/validation.php', "<?php return ['egyptian_mobile' => 'App says :attribute is wrong.'];");

        $this->bootApp('en', [$lang]);

        $this->assertSame('App says phone is wrong.', $this->validate(['phone' => '123'], ['phone' => 'egyptian_mobile'])->errors()->first('phone'));
    }

    public function test_app_can_override_package_translations(): void
    {
        $lang = sys_get_temp_dir().'/egypt-validators-'.bin2hex(random_bytes(4));
        $this->tempDirs[] = $lang;
        mkdir($lang.'/vendor/egypt-validators/ar', 0777, true);
        file_put_contents($lang.'/vendor/egypt-validators/ar/validation.php', "<?php return ['mobile' => 'رقم :attribute غلط.'];");

        $this->bootApp('ar', [$lang]);

        $this->assertSame('رقم phone غلط.', $this->validate(['phone' => '123'], ['phone' => 'egyptian_mobile'])->errors()->first('phone'));
    }

    public function test_provider_works_when_validator_was_resolved_first(): void
    {
        $app = new Container();
        $app->singleton('translator', fn () => new Translator(new FileLoader(new Filesystem(), []), 'ar'));
        $app->singleton('validator', fn (Container $app) => new Factory($app->make('translator'), $app));
        $app->make('validator');

        (new EgyptValidatorsServiceProvider($app))->boot();

        $v = $app->make('validator')->make(['phone' => '123'], ['phone' => 'egyptian_mobile']);
        $this->assertSame('يجب أن يكون phone رقم موبايل مصريًا صحيحًا.', $v->errors()->first('phone'));
    }

    public function test_composer_registers_the_provider_for_auto_discovery(): void
    {
        $composer = json_decode((string) file_get_contents(__DIR__.'/../composer.json'), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame([EgyptValidatorsServiceProvider::class], $composer['extra']['laravel']['providers']);
    }
}
