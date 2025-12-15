<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonalInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone_e164' => ['nullable', 'string', 'regex:/^\+[1-9]\d{1,14}$/'],
            'street_name' => ['nullable', 'string', 'max:255'],
            'street_number' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'regex:/^\d{2}-\d{3}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => __('Imię jest wymagane.'),
            'last_name.required' => __('Nazwisko jest wymagane.'),
            'phone_e164.regex' => __('Numer telefonu musi być w formacie międzynarodowym (np. +48123456789).'),
            'postal_code.regex' => __('Kod pocztowy musi być w formacie XX-XXX.'),
        ];
    }
}
