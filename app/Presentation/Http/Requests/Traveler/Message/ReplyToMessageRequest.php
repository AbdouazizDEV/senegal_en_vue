<?php

namespace App\Presentation\Http\Requests\Traveler\Message;

use Illuminate\Foundation\Http\FormRequest;

class ReplyToMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string|min:10|max:2000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'url|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Le contenu du message est obligatoire.',
            'content.min' => 'Le message doit contenir au moins :min caractères.',
            'content.max' => 'Le message ne peut pas dépasser :max caractères.',
            'attachments.array' => 'Les pièces jointes doivent être un tableau.',
            'attachments.max' => 'Vous ne pouvez pas ajouter plus de :max pièces jointes.',
            'attachments.*.url' => 'Chaque pièce jointe doit être une URL valide.',
        ];
    }
}


