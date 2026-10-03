@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'md-input']) }}>
