{{--
    flux:popover — custom Flux component (not in Flux Free edition).

    A fixed notification-style overlay panel that auto-dismisses after a
    configurable duration and can be manually dismissed. Includes a timing
    progress bar that counts down visually.

    Usage:
        <flux:popover
            :show="$show"
            :duration="8000"
            position="bottom-right"
            @dismissed="show = false"
        >
            Content here
        </flux:popover>

    Props:
        show       — boolean, whether the popover is visible (default false)
        duration   — ms before auto-dismiss (default 8000, set 0 to disable)
        position   — bottom-right | bottom-left | top-right | top-left (default bottom-right)

    Alpine events:
        @dismissed — fired when closed by auto-timer or by user click

    Dismissal via x-dispatch from Livewire:
        $dispatch('banner-dismiss')
--}}
@props([
    'show'     => false,
    'duration' => 8000,
    'position' => 'bottom-right',
])

@php
$positionClasses = match ($position) {
    'bottom-left'  => 'bottom-4 left-4',
    'top-right'    => 'top-4 right-4',
    'top-left'     => 'top-4 left-4',
    default        => 'bottom-4 right-4',
};
@endphp

<div
    x-data="{
        open: @js($show),
        duration: @js((int) $duration),
        progress: 100,
        timer: null,
        progressTimer: null,
        startedAt: null,

        init() {
            this.$watch('open', val => {
                if (val) { this.start(); }
                else { this.clear(); }
            });
            if (this.open) { this.start(); }
        },

        start() {
            if (this.duration <= 0) return;
            this.progress = 100;
            this.startedAt = Date.now();
            const step = 50;
            this.progressTimer = setInterval(() => {
                const elapsed = Date.now() - this.startedAt;
                this.progress = Math.max(0, 100 - (elapsed / this.duration) * 100);
            }, step);
            this.timer = setTimeout(() => this.dismiss(), this.duration);
        },

        clear() {
            clearTimeout(this.timer);
            clearInterval(this.progressTimer);
        },

        dismiss() {
            this.clear();
            this.open = false;
            this.$dispatch('dismissed');
        },
    }"
    x-show="open"
    x-on:banner-dismiss.window="dismiss()"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 translate-y-2 scale-95"
    {{ $attributes->merge(['class' => "fixed z-50 w-full max-w-sm {$positionClasses}"]) }}
    role="dialog"
    aria-modal="true"
    style="display: none;"
>
    <div class="relative overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-800">

        {{-- Dismiss button --}}
        <button
            type="button"
            x-on:click="dismiss()"
            class="absolute top-2 right-2 flex size-7 items-center justify-center rounded-lg text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-200 transition"
            aria-label="{{ __('Dismiss') }}"
        >
            <flux:icon name="x-mark" class="size-4" />
        </button>

        {{-- Content --}}
        <div class="p-4 pr-10">
            {{ $slot }}
        </div>

        {{-- Progress bar (only renders when duration > 0) --}}
        @if ((int) $duration > 0)
        <div class="h-0.5 w-full bg-zinc-100 dark:bg-zinc-700">
            <div
                class="h-full bg-zinc-400 dark:bg-zinc-500 transition-none"
                :style="'width: ' + progress + '%'"
            ></div>
        </div>
        @endif
    </div>
</div>
