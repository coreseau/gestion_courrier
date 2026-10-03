<button {{ $attributes->merge(['type' => 'submit', 'class' => 'md-btn md-btn-primary']) }}>
    {{ $slot }}
</button>
