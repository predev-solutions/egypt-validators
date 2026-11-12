<?php

declare(strict_types=1);

namespace Predev\EgyptValidators\Laravel;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Predev\EgyptValidators\Gender;
use Predev\EgyptValidators\NationalId;

final class EgyptianNationalId implements ValidationRule, ValidatorAwareRule
{
    private ?Validator $validator = null;

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

    public function setValidator(Validator $validator): static
    {
        $this->validator = $validator;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $info = is_scalar($value) ? NationalId::parse((string) $value) : null;

        if ($info === null) {
            $fail($this->message('national_id'));

            return;
        }

        if ($this->minAge !== null && $info->age() < $this->minAge) {
            $fail($this->message('national_id_min_age', ['years' => $this->minAge]));
        }

        if ($this->gender !== null && $info->gender !== $this->gender) {
            $fail($this->message('national_id_'.$this->gender->value));
        }
    }

    /** @param array<string, string|int> $replace */
    private function message(string $key, array $replace = []): string
    {
        return Messages::get($this->validator, $key, $replace);
    }
}
