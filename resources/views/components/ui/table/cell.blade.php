@php
    $classes = [
        'px-4 py-4 text-sm text-neutral-700 dark:text-neutral-300 align-middle',
    ];
@endphp

<td {{ $attributes->class(Arr::toCssClasses($classes)) }}>
    {{ $slot }}
</td>
