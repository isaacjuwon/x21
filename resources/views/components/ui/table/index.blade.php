@props([
    'header' => null,
    'body' => null,
    'footer' => null,
])

@php
    $classes = [
        'w-full text-left border-collapse',
    ];

    $containerClasses = [
        'relative overflow-x-auto border border-black/10 dark:border-white/10 rounded-xl bg-white dark:bg-neutral-900 shadow-sm',
    ];
@endphp

<div {{ $attributes->class(Arr::toCssClasses($containerClasses)) }}>
    <table class="{{ Arr::toCssClasses($classes) }}">
        @if($header)
            <thead class="bg-neutral-50 dark:bg-neutral-800/50 border-b border-black/10 dark:border-white/10">
                <tr>
                    {{ $header }}
                </tr>
            </thead>
        @endif

        <tbody class="divide-y divide-black/5 dark:divide-white/5">
            {{ $body ?? $slot }}
        </tbody>

        @if($footer)
            <tfoot class="bg-neutral-50 dark:bg-neutral-800/50 border-t border-black/10 dark:border-white/10 font-medium">
                {{ $footer }}
            </tfoot>
        @endif
    </table>
</div>
