<?php

namespace App\Presentation\Http\Requests\Traveler\TravelBook;

use Illuminate\Foundation\Http\FormRequest;

class ShareTravelBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visibility' => 'required|string|in:friends,public',
            'entry_ids' => 'nullable|array',
            'entry_ids.*' => 'integer|exists:travelbook_entries,id',
        ];
    }

    public function messages(): array
    {
        return [
            'visibility.required' => 'La visibilité est obligatoire.',
            'visibility.in' => 'La visibilité doit être : friends ou public.',
            'entry_ids.array' => 'Les IDs des entrées doivent être un tableau.',
            'entry_ids.*.exists' => 'Une ou plusieurs entrées sélectionnées n\'existent pas.',
        ];
    }
}


