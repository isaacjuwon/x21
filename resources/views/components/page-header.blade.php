@props(['heading', 'description' => null])

<div {{ $attributes->merge(['class' => 'mb-6']) }}>
    <div class="flex items-start justify-between gap-4">
        <div class="flex-1">
            <x-ui.heading>{{ $heading }}</x-ui.heading>
            
            @if($description)
                <x-ui.description class="mt-1">{{ $description }}</x-ui.description>
            @endif
        </div>
        
        @if(isset($actions))
            <div class="flex items-center gap-2">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
