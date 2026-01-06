<?php

namespace App\Presentation\Http\Requests\Traveler\Message;

use Illuminate\Foundation\Http\FormRequest;

class ContactProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'experience_id' => 'nullable|integer|exists:experiences,id',
            'booking_id' => 'nullable|integer|exists:bookings,id',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|min:10|max:2000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'url|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Le message est obligatoire.',
            'message.min' => 'Le message doit contenir au moins :min caractères.',
            'message.max' => 'Le message ne peut pas dépasser :max caractères.',
            'experience_id.exists' => 'L\'expérience sélectionnée n\'existe pas.',
            'booking_id.exists' => 'La réservation sélectionnée n\'existe pas.',
            'attachments.array' => 'Les pièces jointes doivent être un tableau.',
            'attachments.max' => 'Vous ne pouvez pas ajouter plus de :max pièces jointes.',
            'attachments.*.url' => 'Chaque pièce jointe doit être une URL valide.',
        ];
    }
}


