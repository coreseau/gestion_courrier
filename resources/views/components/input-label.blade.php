@props(['value'])

<label {{ $attributes->merge(['class' => 'md-form-label']) }}>
    {{ $value ?? $slot }}
</label>
