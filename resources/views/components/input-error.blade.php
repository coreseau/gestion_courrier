@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'md-form-error']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
