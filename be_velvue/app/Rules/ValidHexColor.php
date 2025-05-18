<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidHexColor implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a string.');
            return;
        }

        // Check if the value is a valid hex color (with or without # prefix)
        $pattern = '/^#?([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/';

        if (! preg_match($pattern, $value)) {
            $fail('The :attribute must be a valid hex color (e.g., #FFFFFF or #FFF).');
        }
    }
}
