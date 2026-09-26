<?php

declare(strict_types=1);

namespace Predev\EgyptValidators\Laravel;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Predev\EgyptValidators\Gender;
use Predev\EgyptValidators\NationalId;

final class EgyptianNationalId implements ValidationRule
{
    public function __construct(
        private readonly ?int $minAge = null,
        private readonly ?Gender $gender = null,
    ) {
    }

    public static function make(): self
    {
        return new self();
    }

    public function minAge(int $years): self
    {
        return new self($years, $this->gender);
    }

    public function gender(Gender $gender): self
    {
        return new self($this->minAge, $gender);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $info = is_scalar($value) ? NationalId::parse((string) $value) : null;

        if ($info === null) {
            $fail('The :attribute must be a valid Egyptian national ID.');

            return;
        }

        if ($this->minAge !== null && $info->age() < $this->minAge) {
            $fail("The :attribute holder must be at least {$this->minAge} years old.");
        }

        if ($this->gender !== null && $info->gender !== $this->gender) {
            $fail("The :attribute must belong to a {$this->gender->value}.");
        }
    }
}
