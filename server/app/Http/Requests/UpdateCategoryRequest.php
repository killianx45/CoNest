<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && (Auth::user()->role === 'ROLE_ADMIN' || Auth::user()->role === 'ROLE_LOUEUR');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->ignore($this->route('category')),
            ],
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
            'name.required' => 'Le nom de la catégorie est obligatoire',
            'name.string' => 'Le nom de la catégorie doit être une chaîne de caractères',
            'name.max' => 'Le nom de la catégorie ne doit pas dépasser 255 caractères',
            'name.unique' => 'Cette catégorie existe déjà',
        ];
    }
}
