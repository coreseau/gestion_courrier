<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTraitementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('traiter', $this->route('dossier'));
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:brouillon,soumettre'],
            'reference_traitement' => ['nullable', 'string', 'max:100'],
            'contenu' => ['required_if:action,soumettre', 'nullable', 'string', 'min:10', 'max:20000'],
            'pieces' => ['nullable', 'array', 'max:5'],
            'pieces.*' => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:10240'],
        ];
    }

    public function attributes(): array
    {
        return [
            'reference_traitement' => 'référence de traitement',
            'contenu' => 'traitement',
            'pieces' => 'pièces jointes',
            'pieces.*' => 'pièce jointe',
        ];
    }

    public function messages(): array
    {
        return [
            'contenu.required_if' => 'Rédigez votre traitement avant de le soumettre au directeur.',
            'pieces.max' => 'Vous pouvez joindre 5 pièces au maximum à la fois.',
            'pieces.*.max' => 'Chaque pièce ne doit pas dépasser 10 Mo.',
            'pieces.*.mimes' => 'Les pièces doivent être au format PDF, image, Word ou Excel.',
        ];
    }
}