@php
    $classes = [
        'px-4 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400',
    ];
@endphp

<th {{ $attributes->class(Arr::toCssClasses($classes)) }}>
    {{ $slot }}
</th>
