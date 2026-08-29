{{--
    Carousel component — built on Flux UI + Alpine.js (bundled with Livewire 4).

    Usage:
        <x-carousel :items="$banners">
            <x-slot:slide :banner="$banner">
                ...
            </x-slot:slide>
        </x-carousel>

    Or via the banner-slot Livewire component which handles the query:
        <livewire:banners.banner-slot location="wallet" />
--}}
@props([
    'items' => [],         // iterable of Banner models
    'autoPlay' => true,    // whether to auto-advance
    'interval' => 5000,    // ms between auto-advances
])

@if (count($items))
<div
    x-data="{
        current: 0,
        total: {{ count($items) }},
        autoPlay: {{ $autoPlay ? 'true' : 'false' }},
        interval: {{ $interval }},
        timer: null,
        start() {
            if (! this.autoPlay || this.total <= 1) return;
            this.timer = setInterval(() => this.next(), this.interval);
        },
        stop() { clearInterval(this.timer); },
        next() { this.current = (this.current + 1) % this.total; },
        prev() { this.current = (this.current - 1 + this.total) % this.total; },
        goTo(index) { this.current = index; },
    }"
    x-init="start()"
    @mouseenter="stop()"
    @mouseleave="start()"
    {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-xl']) }}
>
    {{-- Slides --}}
    <div class="relative">
        @foreach ($items as $index => $banner)
            <div
                x-show="current === {{ $index }}"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-4"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 -translate-x-4"
                class="w-full"
            >
                {{-- Image --}}
                @if ($banner->hasMedia('image'))
                    <img
                        src="{{ $banner->getFirstMediaUrl('image', 'thumb') }}"
                        alt="{{ $banner->title }}"
                        class="w-full h-40 sm:h-52 object-cover rounded-xl"
                        loading="lazy"
                    />
                @endif

                {{-- Content overlay / body --}}
                @if ($banner->content || $banner->link_url)
                    <div class="mt-3 space-y-2 px-1">
                        @if ($banner->content)
                            <div class="prose prose-sm dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-300">
                                {!! $banner->content !!}
                            </div>
                        @endif

                        @if ($banner->link_url)
                            <flux:button
                                href="{{ $banner->link_url }}"
                                size="sm"
                                variant="primary"
                                class="w-full sm:w-auto"
                            >
                                {{ $banner->link_text ?: __('Learn more') }}
                            </flux:button>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Navigation arrows — only show when more than one slide --}}
    @if (count($items) > 1)
        <button
            @click="prev()"
            type="button"
            class="absolute left-2 top-1/3 -translate-y-1/2 flex items-center justify-center size-8 rounded-full bg-white/70 dark:bg-zinc-800/70 shadow hover:bg-white dark:hover:bg-zinc-700 transition"
            aria-label="{{ __('Previous') }}"
        >
            <flux:icon name="chevron-left" class="size-4 text-zinc-700 dark:text-zinc-200" />
        </button>

        <button
            @click="next()"
            type="button"
            class="absolute right-2 top-1/3 -translate-y-1/2 flex items-center justify-center size-8 rounded-full bg-white/70 dark:bg-zinc-800/70 shadow hover:bg-white dark:hover:bg-zinc-700 transition"
            aria-label="{{ __('Next') }}"
        >
            <flux:icon name="chevron-right" class="size-4 text-zinc-700 dark:text-zinc-200" />
        </button>

        {{-- Dot indicators --}}
        <div class="flex justify-center gap-1.5 mt-3">
            @foreach ($items as $index => $banner)
                <button
                    @click="goTo({{ $index }})"
                    type="button"
                    :class="current === {{ $index }}
                        ? 'bg-zinc-800 dark:bg-zinc-200 w-4'
                        : 'bg-zinc-300 dark:bg-zinc-600 w-2'"
                    class="h-2 rounded-full transition-all duration-300"
                    aria-label="{{ __('Go to slide :n', ['n' => $index + 1]) }}"
                ></button>
            @endforeach
        </div>
    @endif
</div>
@endif
