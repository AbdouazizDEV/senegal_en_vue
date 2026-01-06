<?php

namespace App\Presentation\Http\Requests\Traveler;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'experience_id' => 'required|integer|exists:experiences,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'nullable|date_format:H:i',
            'participants_count' => 'required|integer|min:1',
            'special_requests' => 'nullable|string|max:1000',
            'payment_method' => 'nullable|string|max:50',
            'metadata' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'experience_id.required' => 'L\'expérience est obligatoire.',
            'experience_id.exists' => 'L\'expérience sélectionnée n\'existe pas.',
            'booking_date.required' => 'La date de réservation est obligatoire.',
            'booking_date.date' => 'La date de réservation doit être une date valide.',
            'booking_date.after_or_equal' => 'La date de réservation doit être aujourd\'hui ou une date future.',
            'booking_time.date_format' => 'L\'heure doit être au format HH:mm.',
            'participants_count.required' => 'Le nombre de participants est obligatoire.',
            'participants_count.integer' => 'Le nombre de participants doit être un entier.',
            'participants_count.min' => 'Le nombre de participants doit être au moins de :min.',
            'special_requests.max' => 'Les demandes spéciales ne peuvent pas dépasser :max caractères.',
        ];
    }
}


