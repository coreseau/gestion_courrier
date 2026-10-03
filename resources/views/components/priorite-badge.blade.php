@props(['priorite'])

@php
    $map = [
        'normale' => ['Normale', 'md-badge-neutral'],
        'urgente' => ['Urgente', 'md-badge-warning'],
        'tres_urgente' => ['Très urgente', 'md-badge-error'],
    ];
    [$libelle, $classes] = $map[$priorite] ?? [$priorite, 'md-badge-neutral'];
@endphp

<span {{ $attributes->merge(['class' => "$classes"]) }}>
    {{ $libelle }}
</span>