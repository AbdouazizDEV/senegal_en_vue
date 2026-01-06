<?php

namespace App\Presentation\Http\Requests\Traveler\TravelBook;

use Illuminate\Foundation\Http\FormRequest;

class AddPhotosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photos' => 'required|array|min:1|max:10',
            'photos.*' => 'required|url|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'photos.required' => 'Au moins une photo est requise.',
            'photos.array' => 'Les photos doivent être un tableau.',
            'photos.min' => 'Au moins une photo est requise.',
            'photos.max' => 'Vous ne pouvez pas ajouter plus de :max photos à la fois.',
            'photos.*.required' => 'Chaque photo doit avoir une URL.',
            'photos.*.url' => 'Chaque photo doit être une URL valide.',
            'photos.*.max' => 'L\'URL de la photo ne peut pas dépasser :max caractères.',
        ];
    }
}

