@props([
    'variant' => 'outline',
])

@php
    $classes = Flux::classes('shrink-0')->add(
        match ($variant) {
            'outline' => '[:where(&)]:size-6',
            'mini' => '[:where(&)]:size-5',
            'micro' => '[:where(&)]:size-4',
        },
    );

    $strokeWidth = match ($variant) {
        'outline' => 2,
        'mini' => 2.25,
        'micro' => 2.5,
    };
@endphp

<svg
    {{ $attributes->class($classes) }}
    data-flux-icon
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="{{ $strokeWidth }}"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    data-slot="icon"
>
    <path d="M2.25 18.75a60.07 60.07 0 0 1 15.75-3.28m-9.02 4.12c-.1.32-.2.64-.31.96a.75.75 0 0 1-1.2.32c-.34-.23-.67-.47-1-.72a.75.75 0 0 1-.22-.92c.1-.32.21-.64.31-.96a2.25 2.25 0 0 1 1.48-1.5c.33-.1.67-.19 1-.27m4.5 12.06c.1.32.2.64.31.96a.75.75 0 0 0 1.2.32c.34-.23.67-.47 1-.72a.75.75 0 0 0 .22-.92c-.1-.32-.21-.64-.31-.96a2.25 2.25 0 0 0-1.48-1.5c-.33-.1-.67-.19-1-.27m-11.25 4.5a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6.375 5.25a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75h-.008a.75.75 0 0 1-.75-.75V12.75a.75.75 0 0 1 .75-.75h.008ZM15 17.25a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75h-.008a.75.75 0 0 1-.75-.75V18a.75.75 0 0 1 .75-.75h.008Z" />
</svg>
