<?php

declare(strict_types=1);

namespace Predev\EgyptValidators\Laravel;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Validator;

/**
 * Loads the package translations and registers the string rules
 * `egyptian_mobile` and `egyptian_national_id`.
 */
final class EgyptValidatorsServiceProvider extends ServiceProvider
{
    /**
     * Placeholder message the factory hands back when the app defines no
     * message of its own for a rule. The replacer swaps it for the
     * translated message from the matching rule object.
     */
    private const PENDING = 'egypt-validators::pending';

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../../lang', Messages::NAMESPACE);

        $this->callAfterResolving('validator', function (Factory $factory): void {
            foreach (self::rules() as $name => $make) {
                $factory->extend(
                    $name,
                    fn (string $attribute, mixed $value, array $parameters, Validator $validator): bool => self::message($make(), $attribute, $value, $validator) === null,
                    self::PENDING,
                );

                $factory->replacer(
                    $name,
                    fn (string $message, string $attribute, string $rule, array $parameters, Validator $validator): string => $message === self::PENDING
                        ? str_replace(
                            ':attribute',
                            $validator->getDisplayableAttribute($attribute),
                            self::message($make(), $attribute, Arr::get($validator->getData(), $attribute), $validator) ?? '',
                        )
                        : $message,
                );
            }
        });
    }

    /** @return array<string, Closure(): ValidationRule> */
    public static function rules(): array
    {
        return [
            'egyptian_mobile' => static fn (): ValidationRule => new EgyptianMobile(),
            'egyptian_national_id' => static fn (): ValidationRule => new EgyptianNationalId(),
        ];
    }

    /** Runs the rule and returns its first failure message, or null when the value passes. */
    private static function message(ValidationRule $rule, string $attribute, mixed $value, Validator $validator): ?string
    {
        if ($rule instanceof ValidatorAwareRule) {
            $rule->setValidator($validator);
        }

        $message = null;
        $rule->validate($attribute, $value, function (string $failure) use (&$message): void {
            $message ??= $failure;
        });

        return $message;
    }
}
