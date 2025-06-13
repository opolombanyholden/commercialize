<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuotePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                'min:' . config('app.pdf_password_min_length', 8),
                'max:255'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au minimum :min caractères.',
        ];
    }
}