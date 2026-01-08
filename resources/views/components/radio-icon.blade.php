@props([
    'value',
    'checked' => false,
    'name' => '',
    'icon' => null,
    'alt' => '',
    'label' => ''
])

@php
    $isChecked = old($name, $checked) || (isset($wireModel) && $wireModel === $value);
@endphp

<div class="relative">
    <input 
        type="radio" 
        id="{{ $value }}-{{ $name }}"
        name="{{ $name }}"
        value="{{ $value }}"
        {{ $attributes->merge(['class' => 'peer sr-only']) }}
        wire:model="{{ $name }}"
        @if ($isChecked) checked @endif
    >
    
    <label 
        for="{{ $value }}-{{ $name }}" 
        class="flex flex-col items-center p-3 border-2 border-transparent rounded-full cursor-pointer peer-checked:border-primary peer-checked:shadow-lg peer-checked:shadow-primary/20 transition-all duration-200"
    >
        @if($icon)
            <img 
                src="{{ $icon }}" 
                alt="{{ $alt }}" 
                class="w-12 h-12 rounded-full object-cover"
            >
        @endif
        
        @if($label)
            <span class="mt-2 text-sm font-medium">{{ $label }}</span>
        @endif
    </label>
</div>