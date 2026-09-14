<?php

use App\Models\Banner;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Banner popup slot — Livewire 4 SFC.
 *
 * Loads active, in-schedule banners for the given location and renders each
 * as a stacked, auto-advancing popup card. Banners cycle sequentially: the
 * next shows automatically when the timer for the current one elapses, or
 * immediately if the user dismisses it (when dismissible).
 *
 * Usage:
 *   <livewire:banners.index location="wallet" />
 */
new #[Lazy] class extends Component {
    #[Locked]
    public string $location = '';

    #[Locked]
    public int $duration = 8000; // ms per banner

    /**
     * @return array<int, array{id: int, content: string|null, link_url: string|null, link_text: string|null, dismissible: bool, image_url: string|null}>
     */
    #[Computed]
    public function banners(): array
    {
        if ($this->location === '') {
            return [];
        }

        return Banner::query()
            ->activeForLocation($this->location)
            ->get()
            ->map(fn (Banner $banner) => [
                'id'          => $banner->id,
                'content'     => $banner->content,
                'link_url'    => $banner->link_url,
                'link_text'   => $banner->link_text,
                'dismissible' => $banner->dismissible,
                'image_url'   => $banner->hasMedia('image')
                    ? $banner->getFirstMediaUrl('image', 'thumb')
                    : null,
            ])
            ->values()
            ->all();
    }

    public function placeholder(): string
    {
        return '<div></div>';
    }
}; ?>

@php $banners = $this->banners; $total = count($banners); @endphp

<div>
    @if ($total > 0)
        <div
            x-data="{
                current: 0,
                total: {{ $total }},
                duration: {{ $this->duration }},
                remaining: {{ $this->duration }},
                paused: false,
                timer: null,
                start() {
                    this.remaining = this.duration
                    clearInterval(this.timer)
                    this.timer = setInterval(() => {
                        if (this.paused) return
                        this.remaining -= 100
                        if (this.remaining <= 0) this.next()
                    }, 100)
                },
                next() {
                    if (this.current < this.total - 1) {
                        this.current++
                        this.start()
                    } else {
                        this.current = this.total
                        clearInterval(this.timer)
                    }
                },
            }"
            x-init="start()"
            x-teleport="body"
            class="pointer-events-none fixed inset-x-0 top-4 z-50 flex justify-center px-4 sm:justify-end sm:px-6"
        >
            @foreach ($banners as $index => $banner)
                <div
                    x-show="current === {{ $index }}"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @mouseenter="paused = true"
                    @mouseleave="paused = false"
                    style="display: none;"
                    class="pointer-events-auto relative w-full max-w-sm overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-zinc-950/10 dark:bg-zinc-800 dark:ring-white/10"
                >
                    {{-- timer bar --}}
                    <div class="absolute inset-x-0 top-0 h-1 bg-zinc-100 dark:bg-zinc-700">
                        <div
                            class="h-full bg-zinc-900 dark:bg-white"
                            :style="`width: ${(remaining / duration) * 100}%; transition: width 100ms linear;`"
                        ></div>
                    </div>

                    <div class="flex items-start gap-3 p-4 pt-5">
                        <div class="min-w-0 flex-1">
                            @if ($banner['image_url'])
                                <img
                                    src="{{ $banner['image_url'] }}"
                                    alt=""
                                    class="mb-3 h-28 w-full rounded-lg object-cover"
                                    loading="lazy"
                                />
                            @endif

                            @if ($banner['content'])
                                <div class="prose prose-sm dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-300">
                                    {!! $banner['content'] !!}
                                </div>
                            @endif

                            @if ($banner['link_url'])
                                <a
                                    href="{{ $banner['link_url'] }}"
                                    class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-zinc-900 hover:underline dark:text-white"
                                >
                                    {{ $banner['link_text'] ?: __('Learn more') }}
                                    <flux:icon name="arrow-right" class="size-3" />
                                </a>
                            @endif
                        </div>

                        @if ($banner['dismissible'])
                            <flux:button
                                icon="x-mark"
                                variant="ghost"
                                size="sm"
                                @click="next()"
                                aria-label="{{ __('Dismiss') }}"
                            />
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>