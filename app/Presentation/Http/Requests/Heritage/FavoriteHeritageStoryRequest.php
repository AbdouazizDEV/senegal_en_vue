<?php

namespace App\Presentation\Http\Requests\Heritage;

use Illuminate\Foundation\Http\FormRequest;

class FavoriteHeritageStoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Pas de règles spécifiques, l'ID est dans l'URL
        ];
    }
}


