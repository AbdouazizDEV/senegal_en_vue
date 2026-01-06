<?php

namespace App\Presentation\Http\Requests\Traveler;

use Illuminate\Foundation\Http\FormRequest;

class CancelBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'reason.max' => 'La raison d\'annulation ne peut pas dépasser :max caractères.',
        ];
    }
}


