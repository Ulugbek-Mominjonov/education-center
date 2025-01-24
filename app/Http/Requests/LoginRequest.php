<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'user_name' => 'required',
            'password' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'user_name.required' => 'User name is required.',
            'password.required' => 'Password is required.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors(), // Bu yerda validatsiya qilingan barcha xatoliklar
        ], 422));
    }

}
