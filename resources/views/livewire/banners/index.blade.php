<?php

use App\Models\Banner;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Banner slot — Livewire SFC.
 *
 * Queries active, in-schedule banners for the given location and renders them
 * through the <x-carousel> Blade component. Renders nothing when no banners
 * are active.
 *
 * Usage on any user page:
 *   <livewire:banners.index location="wallet" />
 */
new #[Lazy] class extends Component {
    /** Page location key passed by the parent page. */
    #[Locked]
    public string $location = '';

    /**
     * @return Collection<int, Banner>
     */
    public function banners(): Collection
    {
        if ($this->location === '') {
            return collect();
        }

        return Cache::remember(
            "banners:{$this->location}",
            now()->addMinutes(5),
            fn () => Banner::query()
                ->activeForLocation($this->location)
                ->get()
        );
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="w-full h-40 sm:h-52 rounded-xl bg-zinc-100 dark:bg-zinc-800 animate-pulse mb-4"></div>
        HTML;
    }
}; ?>

@php $banners = $this->banners(); @endphp

@if ($banners->isNotEmpty())
    <x-carousel :items="$banners" class="mb-4" />
@endif
