<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8',
            'name' => 'required|string|max:255',
            'telephone' => 'required|string|max:255',
            'role' => 'sometimes|string|in:ROLE_USER,ROLE_ADMIN,ROLE_LOUEUR',
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
            'email.required' => 'L\'adresse email est obligatoire',
            'email.email' => 'L\'adresse email doit être valide',
            'email.unique' => 'Cette adresse email est déjà utilisée',
            'password.required' => 'Le mot de passe est obligatoire',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',
            'name.required' => 'Le nom est obligatoire',
            'telephone.required' => 'Le numéro de téléphone est obligatoire',
            'role.in' => 'Le rôle doit être ROLE_USER, ROLE_ADMIN ou ROLE_LOUEUR',
        ];
    }
}
