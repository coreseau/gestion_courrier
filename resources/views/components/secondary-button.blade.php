<button {{ $attributes->merge(['type' => 'button', 'class' => 'md-btn md-btn-outlined']) }}>
    {{ $slot }}
</button>
