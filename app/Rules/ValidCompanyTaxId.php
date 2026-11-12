<?php

namespace App\Rules;

use App\Support\Nif\NifValidator;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCompanyTaxId implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! NifValidator::isValid($value)) {
            $fail(__('nif_formato_invalido'));
        }
    }
}
