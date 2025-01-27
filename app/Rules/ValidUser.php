<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidUser implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = User::find($value);

        if (!$user) {
            $fail('The selected user id is invalid.');
            return;
        }

        if (!$user->is_active) {
            $fail('User is not active');
        }

        if ($user->is_attach) {
            $fail('User is already attached');
        }
    }
}
