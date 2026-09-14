<?php

use App\Models\Banner;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Banner popup slot — Livewire 4 SFC.
 *
 * Loads active, in-schedule banners for the given location and renders each
 * as a stacked popup card. Banners cycle sequentially: the next one shows
 * only when the current one is dismissed (dismissible banners only —
 * non-dismissible banners will remain visible indefinitely).
 *
 * Usage:
 *   <livewire:banners.index location="wallet" />
 */
new class extends Component {
    #[Locked]
    public string $location = '';

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

}; ?>

@php $banners = $this->banners; $total = count($banners); @endphp

<div>
    @if ($total > 0)
        <div
            x-data="{
                current: 0,
                total: {{ $total }},
                next() {
                    this.current = this.current < this.total - 1
                        ? this.current + 1
                        : this.total
                },
            }"
        >
        <template x-teleport="body">
            <div class="pointer-events-none fixed inset-0 z-50 flex items-center justify-center p-4">
            @foreach ($banners as $index => $banner)
                <div
                    x-show="current === {{ $index }}"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    style="display: none;"
                    class="pointer-events-auto relative w-full max-w-md overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-zinc-950/10 dark:bg-zinc-800 dark:ring-white/10"
                >
                    <div class="flex items-start gap-3 p-6">
                        <div class="min-w-0 flex-1">
                            @if ($banner['image_url'])
                                <img
                                    src="{{ $banner['image_url'] }}"
                                    alt=""
                                    class="mb-3 w-full rounded-lg"
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
                                variant="ghost"
                                size="sm"
                                class="shrink-0 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:text-zinc-500 dark:hover:bg-zinc-700 dark:hover:text-zinc-300"
                                @click="next()"
                                aria-label="{{ __('Dismiss') }}"
                            >
                                <flux:icon name="x-mark" class="size-4" />
                            </flux:button>
                        @endif
                    </div>
                </div>
            @endforeach
            </div>
        </template>
        </div>
    @endif
</div>