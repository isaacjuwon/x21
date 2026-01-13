@props([
    'balance' => 0,
    'variant' => 'dashboard', // dashboard or full
])

<div {{ $attributes->merge(['class' => 'relative overflow-hidden bg-primary rounded-2xl shadow-lg text-primary-fg ' . ($variant === 'full' ? 'p-8' : 'p-6')]) }}>
    <div class="relative z-10">
        <div class="flex items-center justify-between mb-4">
            <p class="text-primary-fg/80 text-sm font-medium">
                {{ $variant === 'full' ? 'Available Balance' : 'Wallet Balance' }}
            </p>
            <div class="p-2 bg-primary-fg/20 rounded-lg backdrop-blur-sm">
                <x-ui.icon name="wallet" class="w-5 h-5" />
            </div>
        </div>

        <div class="flex items-baseline gap-3 overflow-hidden">
             <p class="{{ $variant === 'full' ? 'text-3xl sm:text-4xl lg:text-5xl' : 'text-2xl sm:text-3xl lg:text-4xl' }} font-bold mb-1 truncate" title="{{ Number::currency($balance) }}">
                {{ Number::currency($balance) }}
            </p>
        </div>

        @if($variant === 'full')
            <p class="text-primary-fg/80 text-sm mt-3">
                <span class="inline-flex items-center gap-1">
                    <x-ui.icon name="shield-check" class="w-4 h-4" />
                    Secured & Encrypted
                </span>
            </p>
        @else
            <p class="text-primary-fg/80 text-sm mb-6">Available funds</p>
        @endif

        @if($slot->isNotEmpty())
            <div class="{{ $variant === 'full' ? 'mt-6' : '' }}">
                {{ $slot }}
            </div>
        @elseif($variant === 'dashboard')
             <x-ui.button 
                size="sm" 
                variant="outline"
                class="w-full justify-center shadow-md hover:shadow-lg transition-shadow border-primary-fg/20 hover:bg-primary-fg/10 text-primary-fg" 
                wire:navigate 
                href="/wallet"
            >
                Manage Wallet
            </x-ui.button>
        @endif
    </div>
    <div class="absolute top-0 right-0 w-32 h-32 bg-primary-fg/5 rounded-full -mr-16 -mt-16"></div>
</div>
