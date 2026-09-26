<?php

declare(strict_types=1);

namespace Predev\EgyptValidators\Laravel;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Predev\EgyptValidators\Carrier;
use Predev\EgyptValidators\Mobile;

final class EgyptianMobile implements ValidationRule
{
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

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $carrier = is_scalar($value) ? Mobile::carrier((string) $value) : null;

        if ($carrier === null) {
            $fail('The :attribute must be a valid Egyptian mobile number.');

            return;
        }

        if ($this->carriers !== [] && ! in_array($carrier, $this->carriers, true)) {
            $fail('The :attribute must be on one of: '.implode(', ', array_map(fn (Carrier $c) => $c->label(), $this->carriers)).'.');
        }
    }
}
