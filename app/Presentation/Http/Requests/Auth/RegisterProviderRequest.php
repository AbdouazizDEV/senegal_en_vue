<?php

namespace App\Presentation\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterProviderRequest extends FormRequest
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
            'bio' => ['sometimes', 'string', 'max:1000'],
            'business_name' => ['required', 'string', 'max:255'],
            'business_registration_number' => ['sometimes', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'region' => ['required', 'string', 'in:dakar,thies,saint-louis,diourbel,fatick,kaffrine,kaolack,kedougou,kolda,louga,matam,sedhiou,tambacounda,ziguinchor'],
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
            'business_name.required' => 'Le nom de l\'entreprise est obligatoire',
            'address.required' => 'L\'adresse est obligatoire',
            'city.required' => 'La ville est obligatoire',
            'region.required' => 'La région est obligatoire',
            'region.in' => 'La région sélectionnée n\'est pas valide',
        ];
    }
}

