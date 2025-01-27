<?php

namespace App\Rules;

use App\Models\Group;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class GroupCapacity implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        foreach ($value as $groupId) {
            $group = Group::find($groupId);

            if (!$group) {
                $fail("This group with Id {$groupId} does not exist.");
                return;
            }

            if ($group->students()->count() >= 18) {
                $fail("This group wiht Id {$groupId} is full.");
            }
        }
    }
}
