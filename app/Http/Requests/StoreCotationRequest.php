<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('coter', $this->route('dossier'));
    }

    public function rules(): array
    {
        return [
            'collaborateurs' => ['required', 'array', 'min:1'],
            'collaborateurs.*' => ['integer'],
            'pilote' => ['required', 'integer', 'in_array:collaborateurs.*'],
            'instruction' => ['nullable', 'string', 'max:2000'],
            'echeance' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $dossier = $this->route('dossier');
            $ids = collect($this->input('collaborateurs'))->map(fn ($v) => (int) $v)->unique();

            $valides = User::role('collaborateur')
                ->where('actif', true)
                ->whereIn('id', $ids)
                ->pluck('id');

            if ($valides->count() !== $ids->count()) {
                $validator->errors()->add('collaborateurs', 'Un des collaborateurs sélectionnés est invalide.');
                return;
            }

            if ($dossier->participantsActifs()->pluck('id')->intersect($ids)->isNotEmpty()) {
                $validator->errors()->add('collaborateurs', 'Un des collaborateurs sélectionnés est déjà coté sur ce dossier.');
            }
        }];
    }

    public function attributes(): array
    {
        return [
            'collaborateurs' => 'collaborateurs',
            'pilote' => 'pilote',
            'instruction' => 'instruction',
            'echeance' => 'échéance',
        ];
    }

    public function messages(): array
    {
        return [
            'collaborateurs.required' => 'Sélectionnez au moins un collaborateur.',
            'collaborateurs.min' => 'Sélectionnez au moins un collaborateur.',
            'pilote.required' => 'Désignez un pilote parmi les collaborateurs sélectionnés.',
            'pilote.in_array' => 'Le pilote doit faire partie des collaborateurs sélectionnés.',
        ];
    }
}