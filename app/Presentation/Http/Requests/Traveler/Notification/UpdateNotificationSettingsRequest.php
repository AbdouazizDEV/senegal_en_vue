<?php

namespace App\Presentation\Http\Requests\Traveler\Notification;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email_enabled' => 'sometimes|boolean',
            'sms_enabled' => 'sometimes|boolean',
            'push_enabled' => 'sometimes|boolean',
            'preferences' => 'nullable|array',
            'preferences.*' => 'array',
            'preferences.*.*' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'email_enabled.boolean' => 'Le paramètre email doit être un booléen.',
            'sms_enabled.boolean' => 'Le paramètre SMS doit être un booléen.',
            'push_enabled.boolean' => 'Le paramètre push doit être un booléen.',
            'preferences.array' => 'Les préférences doivent être un tableau.',
        ];
    }
}


