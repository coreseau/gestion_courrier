<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class TransfertDossierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transferer', $this->route('dossier'));
    }

    public function rules(): array
    {
        return [
            'destinataire' => ['required', 'integer'],
            'motif' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $dossier = $this->route('dossier');

            $destinataire = User::role('collaborateur')
                ->where('actif', true)
                ->find($this->input('destinataire'));

            if (! $destinataire) {
                $validator->errors()->add('destinataire', 'Destinataire invalide.');
            } elseif ($dossier->estCoteActif($destinataire)) {
                $validator->errors()->add('destinataire', 'Ce collaborateur est déjà coté sur ce dossier.');
            }
        }];
    }

    public function attributes(): array
    {
        return [
            'destinataire' => 'destinataire',
            'motif' => 'motif',
        ];
    }
}