<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

class MobileRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (!preg_match('/^1\d{10}+$/', $value)) {
            $fail('Phone number format is incorrect!');
        }
    }

}
