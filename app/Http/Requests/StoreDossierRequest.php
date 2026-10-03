<?php

namespace App\Http\Requests;

use App\Models\Dossier;
use Illuminate\Foundation\Http\FormRequest;

class StoreDossierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Dossier::class);
    }

    public function rules(): array
    {
        return [
            'objet' => ['required', 'string', 'max:255'],
            'resume' => ['nullable', 'string', 'max:5000'],
            'origine_type' => ['required', 'in:direction,entreprise'],
            'origine_nom' => ['required', 'string', 'max:255'],
            'date_reception' => ['required', 'date', 'before_or_equal:today'],
            'priorite' => ['required', 'in:normale,urgente,tres_urgente'],
            'echeance' => ['nullable', 'date', 'after_or_equal:date_reception'],
            'scan' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }

    public function attributes(): array
    {
        return [
            'objet' => 'objet',
            'resume' => 'résumé',
            'origine_type' => "type d'origine",
            'origine_nom' => "nom de l'origine",
            'date_reception' => 'date de réception',
            'priorite' => 'priorité',
            'echeance' => 'échéance',
            'scan' => 'scan',
        ];
    }

    public function messages(): array
    {
        return [
            'scan.max' => 'Le scan ne doit pas dépasser 10 Mo.',
            'scan.mimes' => 'Le scan doit être un fichier PDF, JPG ou PNG.',
        ];
    }
}