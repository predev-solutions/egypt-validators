<?php

declare(strict_types=1);

namespace Predev\EgyptValidators\Laravel;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Predev\EgyptValidators\Carrier;
use Predev\EgyptValidators\Mobile;

final class EgyptianMobile implements ValidationRule, ValidatorAwareRule
{
    private ?Validator $validator = null;

    /** @param list<Carrier> $carriers */
    public function __construct(private readonly array $carriers = [])
    {
    }

    public static function make(): self
    {
        return new self();
    }

    public function carriers(Carrier ...$carriers): self
    {
        return new self(array_values($carriers));
    }

    public function setValidator(Validator $validator): static
    {
        $this->validator = $validator;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $carrier = is_scalar($value) ? Mobile::carrier((string) $value) : null;

        if ($carrier === null) {
            $fail($this->message('mobile'));

            return;
        }

        if ($this->carriers !== [] && ! in_array($carrier, $this->carriers, true)) {
            $fail($this->message('mobile_carriers', [
                'carriers' => implode(', ', array_map(
                    fn (Carrier $c) => $this->message('carriers.'.strtolower($c->name)),
                    $this->carriers,
                )),
            ]));
        }
    }

    /** @param array<string, string|int> $replace */
    private function message(string $key, array $replace = []): string
    {
        return Messages::get($this->validator, $key, $replace);
    }
}
