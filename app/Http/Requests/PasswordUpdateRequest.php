<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PasswordUpdateRequest extends FormRequest
{
   
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'email' => ['required', 'email:filter', 'exists:users,email'],
            'otp' => ['required', 'string', 'min:4', 'max:4'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'], 
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $firstError = $validator->errors()->first();
        throw new HttpResponseException(response()->json([
            'errors' => $firstError,
            'code' => 422
        ], 422));
    }
}
