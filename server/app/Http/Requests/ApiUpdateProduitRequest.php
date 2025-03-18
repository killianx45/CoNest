<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Produit;

class ApiUpdateProduitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $produit = Produit::findOrFail($this->route('produit'));
        return Auth::check() && (Auth::id() === $produit->id_user || Auth::user()->role === 'ROLE_ADMIN' || Auth::user()->role === 'ROLE_LOUEUR');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nom' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'prix' => 'sometimes|numeric|min:0',
            'date_debut' => 'sometimes|date',
            'date_fin' => 'sometimes|date|after_or_equal:date_debut',
            'images' => 'sometimes|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'image_changed' => 'sometimes|in:0,1',
            'categories' => 'sometimes|array',
            'categories.*' => 'exists:categories,id',
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
            'nom.string' => 'Le nom doit être une chaîne de caractères',
            'nom.max' => 'Le nom ne peut pas dépasser 255 caractères',
            'description.string' => 'La description doit être une chaîne de caractères',
            'prix.numeric' => 'Le prix doit être un nombre',
            'prix.min' => 'Le prix doit être supérieur ou égal à 0',
            'date_debut.date' => 'La date de début doit être une date valide',
            'date_fin.date' => 'La date de fin doit être une date valide',
            'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début',
            'images.array' => 'Les images doivent être un tableau',
            'images.*.image' => 'Les images doivent être des images',
            'images.*.mimes' => 'Les images doivent être de type: jpeg, png, jpg, gif',
            'images.*.max' => 'Les images ne peuvent pas dépasser 2Mo',
            'image_changed.in' => 'La valeur de image_changed doit être 0 ou 1',
            'categories.array' => 'Les catégories doivent être un tableau',
            'categories.*.exists' => 'Une des catégories sélectionnées n\'existe pas',
        ];
    }
}
