<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email_marketing' => ['sometimes', 'boolean'],
            'email_newsletter' => ['sometimes', 'boolean'],
            'sms_consent' => ['sometimes', 'boolean'],
            'sms_marketing' => ['sometimes', 'boolean'],
        ];
    }
}
