<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Tout le monde peut vérifier un OTP
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => [
                'required',
                'string',
            ],
            'otp' => [
                'required',
                'string',
                'size:6',
                'regex:/^[0-9]{6}$/',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'token.required' => 'Le token temporaire est obligatoire pour vérifier l\'OTP.',
            'token.string' => 'Le token temporaire doit être une chaîne de caractères.',
            'otp.required' => 'Le code OTP est obligatoire.',
            'otp.string' => 'Le code OTP doit être une chaîne de caractères.',
            'otp.size' => 'Le code OTP doit contenir exactement 6 caractères.',
            'otp.regex' => 'Le code OTP doit contenir uniquement des chiffres.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'token' => 'token temporaire',
            'otp' => 'code OTP',
        ];
    }
}
