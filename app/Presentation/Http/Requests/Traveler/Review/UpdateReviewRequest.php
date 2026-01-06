<?php

namespace App\Presentation\Http\Requests\Traveler\Review;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => 'sometimes|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'sometimes|string|min:10|max:2000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'url|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'rating.integer' => 'La note doit être un nombre entier.',
            'rating.min' => 'La note doit être au moins :min.',
            'rating.max' => 'La note ne peut pas dépasser :max.',
            'comment.min' => 'Le commentaire doit contenir au moins :min caractères.',
            'comment.max' => 'Le commentaire ne peut pas dépasser :max caractères.',
            'title.max' => 'Le titre ne peut pas dépasser :max caractères.',
            'images.array' => 'Les images doivent être un tableau.',
            'images.max' => 'Vous ne pouvez pas ajouter plus de :max images.',
            'images.*.url' => 'Chaque image doit être une URL valide.',
        ];
    }
}


