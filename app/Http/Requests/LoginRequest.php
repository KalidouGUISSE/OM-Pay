<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'numeroTelephone' => 'required|string',
            'codePing' => 'required|string|min:4',
        ];
    }

    public function messages()
    {
        return [
            'numeroTelephone.required' => 'Le numéro de téléphone est obligatoire.',
            'numeroTelephone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'codePing.required' => 'Le code PIN est obligatoire.',
            'codePing.string' => 'Le code PIN doit être une chaîne de caractères.',
            // 'codePing.min' => 'Le code PIN doit contenir au moins :4 caractères.',
        ];
    }

}
