<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Produit;

class UpdateProduitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $produit = Produit::findOrFail($this->route('produit'));
        return Auth::check() && (Auth::id() === $produit->id_user || Auth::user()->role !== 'ROLE_USER');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nom' => 'required',
            'description' => 'required',
            'prix' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'categories' => 'required|array',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
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
            'nom.required' => 'Le nom du produit est obligatoire',
            'description.required' => 'La description du produit est obligatoire',
            'prix.required' => 'Le prix du produit est obligatoire',
            'prix.numeric' => 'Le prix doit être un nombre',
            'image.image' => 'Le fichier doit être une image',
            'image.mimes' => 'L\'image doit être au format jpeg, png, jpg ou gif',
            'image.max' => 'L\'image ne doit pas dépasser 2Mo',
            'categories.required' => 'Vous devez sélectionner au moins une catégorie',
            'categories.array' => 'Les catégories doivent être un tableau',
            'date_debut.required' => 'La date de début est obligatoire',
            'date_debut.date' => 'La date de début doit être une date valide',
            'date_fin.required' => 'La date de fin est obligatoire',
            'date_fin.date' => 'La date de fin doit être une date valide',
            'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début',
        ];
    }
}
