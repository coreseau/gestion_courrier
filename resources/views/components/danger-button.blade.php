<button {{ $attributes->merge(['type' => 'submit', 'class' => 'md-btn md-btn-danger']) }}>
    {{ $slot }}
</button>
