<?php

use App\Models\Banner;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Banner popover slot — Livewire 4 SFC.
 *
 * Loads active, in-schedule banners for the given location and renders each
 * one as a flux:popover notification overlay. When there are multiple banners
 * they are shown sequentially: the next one auto-shows when the previous is
 * dismissed.
 *
 * Usage:
 *   <livewire:banners.index location="wallet" />
 *
 * The location value maps to a BannerLocation enum case value string.
 */
new #[Lazy] class extends Component {
    #[Locked]
    public string $location = '';

    /**
     * @return array<int, array{id: int, title: string, content: string|null, link_url: string|null, link_text: string|null, image_url: string|null, duration: int}>
     */
    public function banners(): array
    {
        if ($this->location === '') {
            return [];
        }

        return Cache::remember(
            "banners:data:{$this->location}",
            now()->addMinutes(5),
            function () {
                return Banner::query()
                    ->activeForLocation($this->location)
                    ->get()
                    ->map(fn (Banner $banner) => [
                        'id'       => $banner->id,
                        'title'    => $banner->title,
                        'content'  => $banner->content,
                        'link_url' => $banner->link_url,
                        'link_text'=> $banner->link_text,
                        'image_url'=> $banner->hasMedia('image')
                            ? $banner->getFirstMediaUrl('image', 'thumb')
                            : null,
                        'duration' => 8000, // ms per banner
                    ])
                    ->values()
                    ->all();
            }
        );
    }

    public function placeholder(): string
    {
        return '<div></div>';
    }
}; ?>

@php $banners = $this->banners(); @endphp

@if (count($banners) > 0)
<div
    x-data="{
        banners: @js($banners),
        current: 0,
        show: true,

        get banner() { return this.banners[this.current] ?? null; },

        next() {
            if (this.current < this.banners.length - 1) {
                this.current++;
                this.show = true;
            } else {
                this.show = false;
            }
        },
    }"
    x-init="show = banners.length > 0"
>
    <template x-for="(banner, index) in banners" :key="banner.id">
        <flux:popover
            x-bind:show="show && current === index"
            x-bind:duration="banner.duration"
            @dismissed="next()"
        >
            {{-- Image --}}
            <template x-if="banner.image_url">
                <img
                    :src="banner.image_url"
                    :alt="banner.title"
                    class="mb-3 w-full h-28 rounded-lg object-cover"
                    loading="lazy"
                />
            </template>

            {{-- HTML content --}}
            <template x-if="banner.content">
                <div
                    class="prose prose-sm dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-300 mb-3"
                    x-html="banner.content"
                ></div>
            </template>

            {{-- CTA --}}
            <template x-if="banner.link_url">
                <a
                    :href="banner.link_url"
                    class="inline-flex items-center gap-1 text-sm font-medium text-zinc-900 dark:text-white hover:underline"
                >
                    <span x-text="banner.link_text || '{{ __('Learn more') }}'"></span>
                    <flux:icon name="arrow-right" class="size-3" />
                </a>
            </template>
        </flux:popover>
    </template>
</div>
@endif
