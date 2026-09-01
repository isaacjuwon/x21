{{--
    Carousel component — built on Flux UI + Alpine.js (bundled with Livewire 4).

    Usage:
        <x-carousel :items="$banners">
            <x-slot:slide :banner="$banner">
                ...
            </x-slot:slide>
        </x-carousel>

    Or via the banner-slot Livewire component which handles the query:
        <livewire:banners.index location="wallet" />
--}}
@props([
    'items' => [],         // iterable of Banner models
    'autoPlay' => true,    // whether to auto-advance
    'interval' => 5000,    // ms between auto-advances
])

@if (count($items))
<div
    x-data="{
        items: {{ Js::from(collect($items)->map(fn ($b) => ['id' => $b->id, 'is_dismissible' => (bool) $b->is_dismissible])) }},
        dismissed: [],
        currentId: null,
        autoPlay: {{ $autoPlay ? 'true' : 'false' }},
        interval: {{ $interval }},
        timer: null,
        init() {
            try {
                this.dismissed = JSON.parse(localStorage.getItem('dismissed_banners') || '[]');
            } catch (e) {
                this.dismissed = [];
            }
            const visible = this.visibleIds();
            if (visible.length > 0) {
                this.currentId = visible[0];
                this.start();
            }
        },
        visibleIds() {
            return this.items
                .filter(item => !this.dismissed.includes(item.id))
                .map(item => item.id);
        },
        visibleCount() {
            return this.visibleIds().length;
        },
        start() {
            this.stop();
            if (!this.autoPlay || this.visibleCount() <= 1) return;
            this.timer = setInterval(() => this.next(), this.interval);
        },
        stop() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },
        next() {
            const visible = this.visibleIds();
            if (visible.length <= 1) return;
            const idx = visible.indexOf(this.currentId);
            const nextIdx = (idx + 1) % visible.length;
            this.currentId = visible[nextIdx];
        },
        prev() {
            const visible = this.visibleIds();
            if (visible.length <= 1) return;
            const idx = visible.indexOf(this.currentId);
            const prevIdx = (idx - 1 + visible.length) % visible.length;
            this.currentId = visible[prevIdx];
        },
        goTo(id) {
            this.currentId = id;
        },
        dismiss(id) {
            if (!this.dismissed.includes(id)) {
                this.dismissed.push(id);
                try {
                    localStorage.setItem('dismissed_banners', JSON.stringify(this.dismissed));
                } catch (e) {}
            }
            const visible = this.visibleIds();
            if (visible.length > 0) {
                this.currentId = visible[0];
                this.start();
            } else {
                this.currentId = null;
                this.stop();
            }
        }
    }"
    x-show="visibleCount() > 0"
    x-cloak
    @mouseenter="stop()"
    @mouseleave="start()"
    {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-xl']) }}
>
    {{-- Slides --}}
    <div class="relative">
        @foreach ($items as $index => $banner)
            <div
                x-show="currentId === {{ $banner->id }}"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-4"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 -translate-x-4"
                class="w-full relative"
            >
                {{-- Dismiss Button --}}
                @if ($banner->is_dismissible)
                    <button
                        @click.stop="dismiss({{ $banner->id }})"
                        type="button"
                        class="absolute right-3 top-3 z-20 flex items-center justify-center size-7 rounded-full bg-black/40 hover:bg-black/70 text-white transition backdrop-blur-sm shadow cursor-pointer"
                        aria-label="{{ __('Dismiss banner') }}"
                        title="{{ __('Dismiss') }}"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif

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

    {{-- Navigation arrows — only show when more than one visible slide --}}
    <template x-if="visibleCount() > 1">
        <div>
            <button
                @click="prev()"
                type="button"
                class="absolute left-2 top-1/3 -translate-y-1/2 z-10 flex items-center justify-center size-8 rounded-full bg-white/70 dark:bg-zinc-800/70 shadow hover:bg-white dark:hover:bg-zinc-700 transition cursor-pointer"
                aria-label="{{ __('Previous') }}"
            >
                <flux:icon name="chevron-left" class="size-4 text-zinc-700 dark:text-zinc-200" />
            </button>

            <button
                @click="next()"
                type="button"
                class="absolute right-2 top-1/3 -translate-y-1/2 z-10 flex items-center justify-center size-8 rounded-full bg-white/70 dark:bg-zinc-800/70 shadow hover:bg-white dark:hover:bg-zinc-700 transition cursor-pointer"
                aria-label="{{ __('Next') }}"
            >
                <flux:icon name="chevron-right" class="size-4 text-zinc-700 dark:text-zinc-200" />
            </button>

            {{-- Dot indicators --}}
            <div class="flex justify-center gap-1.5 mt-3">
                @foreach ($items as $index => $banner)
                    <button
                        x-show="!dismissed.includes({{ $banner->id }})"
                        @click="goTo({{ $banner->id }})"
                        type="button"
                        :class="currentId === {{ $banner->id }}
                            ? 'bg-zinc-800 dark:bg-zinc-200 w-4'
                            : 'bg-zinc-300 dark:bg-zinc-600 w-2'"
                        class="h-2 rounded-full transition-all duration-300 cursor-pointer"
                        aria-label="{{ __('Go to slide :n', ['n' => $index + 1]) }}"
                    ></button>
                @endforeach
            </div>
        </div>
    </template>
</div>
@endif
