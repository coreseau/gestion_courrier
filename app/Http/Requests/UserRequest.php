<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        $utilisateur = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($utilisateur?->id),
            ],
            'fonction' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'in:admin,directeur,collaborateur'],
            'password' => ['nullable', 'string', 'min:8'], // utilisé seulement à la création
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nom',
            'email' => 'adresse courriel',
            'fonction' => 'fonction',
            'role' => 'rôle',
            'password' => 'mot de passe',
        ];
    }
}