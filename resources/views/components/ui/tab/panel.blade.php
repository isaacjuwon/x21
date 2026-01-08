@aware(['variant' => 'outlined'])

@props([
    'name' => null
])
@php
$classes = match($variant){
    'outlined' => [
        'border border-black/10 dark:border-white/10 rounded-box',
        'bg-white p-3 dark:bg-neutral-800',
    ],
    'non-contained' => ['mt-2'],
    default => []
};
@endphp

<div
    {{ $attributes->class($classes) }}
    data-slot="tabs-panel"
    style="display:none;"
    @if(filled($name))data-name="{{ $name }}"@endif
    x-show="isActive({ name: @js($name), index: $el.dataset.panelOrder });"
>
    {{ $slot }}
</div>
