@php
    $classes = [
        'group transition-colors duration-200 hover:bg-neutral-50 dark:hover:bg-white/5',
    ];
@endphp

<tr {{ $attributes->class(Arr::toCssClasses($classes)) }}>
    {{ $slot }}
</tr>
