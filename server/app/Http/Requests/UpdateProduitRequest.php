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
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'categories' => 'required|array',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'adresse' => 'required|string|max:255',
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
            'images.array' => 'Les images doivent être un tableau',
            'images.*.image' => 'Les images doivent être des images',
            'images.*.mimes' => 'Les images doivent être au format jpeg, png, jpg, gif ou webp',
            'images.*.max' => 'Les images ne doivent pas dépasser 2Mo',
            'categories.required' => 'Vous devez sélectionner au moins une catégorie',
            'categories.array' => 'Les catégories doivent être un tableau',
            'date_debut.required' => 'La date de début est obligatoire',
            'date_debut.date' => 'La date de début doit être une date valide',
            'date_fin.required' => 'La date de fin est obligatoire',
            'date_fin.date' => 'La date de fin doit être une date valide',
            'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début',
            'adresse.required' => 'L\'adresse est obligatoire',
            'adresse.string' => 'L\'adresse doit être une chaîne de caractères',
            'adresse.max' => 'L\'adresse ne peut pas dépasser 255 caractères',
        ];
    }
}
