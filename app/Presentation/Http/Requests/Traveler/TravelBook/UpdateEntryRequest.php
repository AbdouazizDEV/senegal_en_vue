<?php

namespace App\Presentation\Http\Requests\Traveler\TravelBook;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string|max:5000',
            'entry_date' => 'sometimes|date',
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
            'title.max' => 'Le titre ne peut pas dépasser :max caractères.',
            'content.max' => 'Le contenu ne peut pas dépasser :max caractères.',
            'entry_date.date' => 'La date d\'entrée doit être une date valide.',
            'visibility.in' => 'La visibilité doit être : private, friends ou public.',
        ];
    }
}

