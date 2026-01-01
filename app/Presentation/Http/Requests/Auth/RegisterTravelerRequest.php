<?php

namespace App\Presentation\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterTravelerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'preferences' => ['sometimes', 'array'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire',
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'L\'email doit être valide',
            'email.unique' => 'Cet email est déjà utilisé',
            'phone.required' => 'Le numéro de téléphone est obligatoire',
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé',
            'password.required' => 'Le mot de passe est obligatoire',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas',
        ];
    }
}

