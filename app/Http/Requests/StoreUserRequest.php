<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'user_name' => 'required|string|unique:users,user_name',
            'user_type_id' => 'required|integer|exists:user_types,id',
            'is_attach' => 'boolean',
            'password' => 'string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'user_name.required' => 'User name is required.',
            'user_name.unique' => 'This user name is already taken.',
            'user_type_id.required' => 'User type is required.',
            'user_type_id.integer' => 'User type must be an integer.',
            'user_type_id.exists' => 'User type does not exist.',
            'is_attach.boolean' => 'is_attach must be a boolean.'
        ];
    }
}
