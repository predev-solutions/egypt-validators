<?php

declare(strict_types=1);

namespace Predev\EgyptValidators\Laravel;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\Validation\Validator;

/**
 * Resolves the rules' messages from the `egypt-validators` translation
 * namespace, falling back to the bundled English text when the app's
 * translator has no entry for the key.
 *
 * @internal
 */
final class Messages
{
    public const NAMESPACE = 'egypt-validators';

    /** @var array<string, mixed>|null */
    private static ?array $english = null;

    private function __construct()
    {
    }

    /** @param array<string, string|int> $replace */
    public static function get(?Validator $validator, string $key, array $replace = []): string
    {
        $translator = $validator !== null && method_exists($validator, 'getTranslator')
            ? $validator->getTranslator()
            : null;

        if ($translator instanceof Translator) {
            return self::translate($translator, $key, $replace);
        }

        return self::fallback($key, $replace);
    }

    /** @param array<string, string|int> $replace */
    private static function translate(Translator $translator, string $key, array $replace): string
    {
        $fullKey = self::NAMESPACE.'::validation.'.$key;

        if (method_exists($translator, 'has') && $translator->has($fullKey)) {
            $message = $translator->get($fullKey, $replace);

            if (is_string($message)) {
                return $message;
            }
        }

        return self::fallback($key, $replace);
    }

    /** @param array<string, string|int> $replace */
    private static function fallback(string $key, array $replace): string
    {
        self::$english ??= require __DIR__.'/../../lang/en/validation.php';

        $message = self::$english;
        foreach (explode('.', $key) as $segment) {
            $message = is_array($message) ? ($message[$segment] ?? null) : null;
        }

        if (! is_string($message)) {
            return $key;
        }

        $pairs = [];
        foreach ($replace as $name => $value) {
            $pairs[':'.$name] = (string) $value;
        }

        return strtr($message, $pairs);
    }
}
