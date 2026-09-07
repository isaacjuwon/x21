<?php

use App\Models\Banner;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Banner popover slot — Livewire 4 SFC.
 *
 * Loads active, in-schedule banners for the given location and renders each
 * as a flux:popover notification overlay. Banners cycle sequentially: the
 * next shows automatically when the previous is dismissed.
 *
 * Usage:
 *   <livewire:banners.index location="wallet" />
 */
new #[Lazy] class extends Component {
    #[Locked]
    public string $location = '';

    /**
     * @return array<int, array{id: int, content: string|null, link_url: string|null, link_text: string|null, image_url: string|null}>
     */
    public function banners(): array
    {
        if ($this->location === '') {
            return [];
        }

        return Cache::remember(
            "banners:data:{$this->location}",
            now()->addMinutes(5),
            fn () => Banner::query()
                ->activeForLocation($this->location)
                ->get()
                ->map(fn (Banner $banner) => [
                    'id'        => $banner->id,
                    'content'   => $banner->content,
                    'link_url'  => $banner->link_url,
                    'link_text' => $banner->link_text,
                    'image_url' => $banner->hasMedia('image')
                        ? $banner->getFirstMediaUrl('image', 'thumb')
                        : null,
                ])
                ->values()
                ->all()
        );
    }

    public function placeholder(): string
    {
        return '<div></div>';
    }
}; ?>

@php $banners = $this->banners(); $total = count($banners); @endphp

<div>
    @if ($total > 0)
        {{--
            Alpine manages which popover index is currently visible.
            Each flux:popover is rendered by Blade server-side; Alpine drives
            show/hide based on the `current` index. On dismiss the next one
            auto-opens. Duration is 8 000 ms per banner.
        --}}
        <div x-data="{ current: 0, total: {{ $total }} }">
            @foreach ($banners as $index => $banner)
                <flux:popover
                    x-bind:show="current === {{ $index }}"
                    :duration="8000"
                    @dismissed="current < total - 1 ? current++ : current = total"
                >
                    @if ($banner['image_url'])
                        <img
                            src="{{ $banner['image_url'] }}"
                            alt=""
                            class="mb-3 w-full h-28 rounded-lg object-cover"
                            loading="lazy"
                        />
                    @endif

                    @if ($banner['content'])
                        <div class="prose prose-sm dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-300 mb-3">
                            {!! $banner['content'] !!}
                        </div>
                    @endif

                    @if ($banner['link_url'])
                        <a
                            href="{{ $banner['link_url'] }}"
                            class="inline-flex items-center gap-1 text-sm font-medium text-zinc-900 dark:text-white hover:underline"
                        >
                            {{ $banner['link_text'] ?: __('Learn more') }}
                            <flux:icon name="arrow-right" class="size-3" />
                        </a>
                    @endif
                </flux:popover>
            @endforeach
        </div>
    @endif
</div>
