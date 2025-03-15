<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifierDisponibiliteRequest extends FormRequest
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
            'produit_id' => 'required|exists:produits,id',
            'date' => 'required|date',
            'heure_debut' => 'required',
            'heure_fin' => 'required',
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
            'produit_id.required' => 'L\'identifiant du produit est obligatoire',
            'produit_id.exists' => 'Le produit sélectionné n\'existe pas',
            'date.required' => 'La date est obligatoire',
            'date.date' => 'La date doit être valide',
            'heure_debut.required' => 'L\'heure de début est obligatoire',
            'heure_fin.required' => 'L\'heure de fin est obligatoire',
        ];
    }
}
