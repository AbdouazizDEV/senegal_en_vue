<?php

namespace App\Presentation\Http\Requests\Traveler\TravelBook;

use Illuminate\Foundation\Http\FormRequest;

class CreateEntryRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'entry_date' => 'required|date',
            'location' => 'nullable|string|max:255',
            'location_details' => 'nullable|array',
            'location_details.address' => 'nullable|string|max:255',
            'location_details.city' => 'nullable|string|max:100',
            'location_details.region' => 'nullable|string|max:100',
            'location_details.coordinates.lat' => 'nullable|numeric',
            'location_details.coordinates.lng' => 'nullable|numeric',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'visibility' => 'nullable|string|in:private,friends,public',
            'metadata' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est obligatoire.',
            'title.max' => 'Le titre ne peut pas dépasser :max caractères.',
            'content.required' => 'Le contenu est obligatoire.',
            'content.max' => 'Le contenu ne peut pas dépasser :max caractères.',
            'entry_date.required' => 'La date d\'entrée est obligatoire.',
            'entry_date.date' => 'La date d\'entrée doit être une date valide.',
            'experience_id.exists' => 'L\'expérience sélectionnée n\'existe pas.',
            'booking_id.exists' => 'La réservation sélectionnée n\'existe pas.',
            'visibility.in' => 'La visibilité doit être : private, friends ou public.',
        ];
    }
}


