<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Commande;

class UpdateCommandeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $commande = Commande::findOrFail($this->route('commande'));
        return Auth::check() && (Auth::id() === $commande->id_user || Auth::user()->role !== 'ROLE_USER');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'produits' => 'required|array',
            'produits.*' => 'exists:produits,id',
            'dates' => 'required|array',
            'dates.*' => 'required|date',
            'heures_debut' => 'required|array',
            'heures_debut.*' => 'required',
            'heures_fin' => 'required|array',
            'heures_fin.*' => 'required',
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
            'produits.required' => 'Vous devez sélectionner au moins un produit',
            'produits.array' => 'Les produits doivent être un tableau',
            'produits.*.exists' => 'Un des produits sélectionnés n\'existe pas',
            'dates.required' => 'Vous devez sélectionner au moins une date',
            'dates.array' => 'Les dates doivent être un tableau',
            'dates.*.required' => 'Toutes les dates sont obligatoires',
            'dates.*.date' => 'Les dates doivent être valides',
            'heures_debut.required' => 'Vous devez sélectionner au moins une heure de début',
            'heures_debut.array' => 'Les heures de début doivent être un tableau',
            'heures_debut.*.required' => 'Toutes les heures de début sont obligatoires',
            'heures_fin.required' => 'Vous devez sélectionner au moins une heure de fin',
            'heures_fin.array' => 'Les heures de fin doivent être un tableau',
            'heures_fin.*.required' => 'Toutes les heures de fin sont obligatoires',
        ];
    }
}
