<?php

namespace App\Http\Requests;

use App\Models\Group;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class StoreTeacherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {
        return [
            'first_name' => 'required',
            'last_name' => 'required',
            'degree' => 'required',
            'salary' => 'numeric|digits_between:7,8',
            'user_id' => 'integer|exists:users,id',
            "subjects" => "required|array",
            "groups" => [
                'array',
                function ($attribute, $value, $fail) use ($request) {
                    $subjectIds = $request->input('subjects', []);

                    $groups = Group::whereIn('id', $value)->get(['id', 'subject_id']);

                    foreach ($value as $groupId) {
                        $group = $groups->firstWhere('id', $groupId);

                        if (!$group || ($group->subject_id !== null && !in_array($group->subject_id, $subjectIds))) {
                            $fail("The {$attribute} value contains an invalid group ID: {$groupId}");
                        }
                    }
                },
            ]
        ];
    }
}
