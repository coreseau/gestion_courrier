@props(['statut'])

@php
    $map = [
        'recu' => ['Reçu', 'md-badge-neutral'],
        'cote' => ['Coté', 'md-badge-primary'],
        'en_traitement' => ['En traitement', 'md-badge-warning'],
        'soumis' => ['Soumis', 'md-badge-primary'],
        'valide' => ['Validé', 'md-badge-success'],
        'a_reprendre' => ['À reprendre', 'md-badge-error'],
        'cloture' => ['Clôturé', 'bg-slate-800 text-white'],
    ];
    [$libelle, $classes] = $map[$statut] ?? [$statut, 'md-badge-neutral'];
@endphp

<span {{ $attributes->merge(['class' => "$classes"]) }}>
    {{ $libelle }}
</span>