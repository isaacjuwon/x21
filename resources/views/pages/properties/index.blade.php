<?php

namespace App\Livewire\Pages\Properties;

use App\Enums\PropertyListingType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Property;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $type = null;
    public ?string $listing_type = null;
    public ?string $min_price = null;
    public ?string $max_price = null;
    public ?string $bedrooms = null;

    #[Computed]
    public function properties()
    {
        return Property::query()
            ->where('is_active', true)
            ->where('status', PropertyStatus::Available)
            ->when($this->search, function ($q) {
                $q->where(function($sq) {
                    $sq->where('title', 'like', '%' . $this->search . '%')
                       ->orWhere('city', 'like', '%' . $this->search . '%')
                       ->orWhere('address', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->listing_type, fn ($q) => $q->where('listing_type', $this->listing_type))
            ->when($this->min_price, fn ($q) => $q->where('price', '>=', $this->min_price))
            ->when($this->max_price, fn ($q) => $q->where('price', '<=', $this->max_price))
            ->when($this->bedrooms, fn ($q) => $q->where('bedrooms', '>=', $this->bedrooms))
            ->latest()
            ->paginate(12);
    }

    #[Computed]
    public function types()
    {
        return PropertyType::cases();
    }

    #[Computed]
    public function listingTypes()
    {
        return PropertyListingType::cases();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'type', 'listing_type', 'min_price', 'max_price', 'bedrooms']);
    }

    public function render()
    {
        return $this->view()->layout('layouts::app');
    }
}; ?>

<div class="max-w-7xl mx-auto p-6 space-y-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-neutral-900 dark:text-white tracking-tight">Discover Properties</h1>
            <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400 font-medium uppercase tracking-widest">Premium real estate for your next move</p>
        </div>
        <div class="flex items-center gap-2">
            <x-ui.button wire:click="resetFilters" variant="ghost" size="sm" class="text-[10px] font-bold uppercase tracking-widest">
                <x-ui.icon name="arrow-path" class="w-3 h-3 mr-1" />
                Reset Filters
            </x-ui.button>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white dark:bg-neutral-800 p-4 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Search by location or name..." class="text-xs font-medium">
            <x-slot:leading>
                <x-ui.icon name="magnifying-glass" class="w-4 h-4 text-neutral-400" />
            </x-slot:leading>
        </x-ui.input>

        <x-ui.select wire:model.live="type" class="text-xs font-medium">
            <x-ui.select.option value="">All Property Types</x-ui.select.option>
            @foreach($this->types as $t)
                <x-ui.select.option value="{{ $t->value }}">{{ $t->getLabel() }}</x-ui.select.option>
            @endforeach
        </x-ui.select>

        <x-ui.select wire:model.live="listing_type" class="text-xs font-medium">
            <x-ui.select.option value="">All Listing Types</x-ui.select.option>
            @foreach($this->listingTypes as $lt)
                <x-ui.select.option value="{{ $lt->value }}">{{ $lt->getLabel() }}</x-ui.select.option>
            @endforeach
        </x-ui.select>

        <div class="flex gap-2">
            <x-ui.input wire:model.live.debounce.300ms="min_price" type="number" placeholder="Min Price" class="text-xs font-medium" />
            <x-ui.input wire:model.live.debounce.300ms="max_price" type="number" placeholder="Max Price" class="text-xs font-medium" />
        </div>
    </div>

    <!-- Results Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        @forelse($this->properties as $property)
            <div class="group bg-white dark:bg-neutral-800 rounded-[--radius-box] border border-neutral-100 dark:border-neutral-700 overflow-hidden hover:shadow-2xl transition-all duration-500 hover:-translate-y-1">
                <!-- Image Container -->
                <div class="relative overflow-hidden aspect-[4/3]">
                    @if($property->main_image)
                        <img src="{{ $property->main_image }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" alt="{{ $property->title }}">
                    @else
                        <div class="w-full h-full bg-neutral-100 dark:bg-neutral-900 flex flex-col items-center justify-center gap-2">
                            <x-ui.icon name="photo" class="w-12 h-12 text-neutral-300" />
                            <span class="text-[10px] font-bold text-neutral-400 uppercase tracking-widest">No Image Available</span>
                        </div>
                    @endif
                    
                    <div class="absolute top-4 left-4 flex flex-col gap-2">
                        <x-ui.badge :color="$property->listing_type === PropertyListingType::Sale ? 'primary' : 'secondary'" class="text-[10px] font-bold uppercase ring-2 ring-white dark:ring-neutral-800">
                            For {{ $property->listing_type->getLabel() }}
                        </x-ui.badge>
                    </div>

                    <div class="absolute bottom-4 left-4">
                        <span class="inline-flex items-center px-3 py-1 bg-black/60 backdrop-blur-md text-white rounded-full text-[10px] font-bold uppercase tracking-wider">
                            {{ $property->type->getLabel() }}
                        </span>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6 space-y-4">
                    <div class="space-y-1">
                        <h3 class="font-bold text-neutral-900 dark:text-white line-clamp-1 group-hover:text-primary transition-colors">{{ $property->title }}</h3>
                        <div class="flex items-center text-[10px] text-neutral-500 font-bold uppercase tracking-widest">
                            <x-ui.icon name="map-pin" class="w-3 h-3 mr-1 text-primary" />
                            {{ $property->city }}, {{ $property->state }}
                        </div>
                    </div>

                    <div class="flex items-center justify-between py-4 border-y border-neutral-50 dark:border-neutral-700/50">
                        <div class="flex items-center gap-1.5">
                            <x-ui.icon name="square-2-stack" class="w-4 h-4 text-neutral-400" />
                            <span class="text-xs font-bold text-neutral-700 dark:text-neutral-300">{{ $property->bedrooms }} <span class="text-[10px] font-medium text-neutral-500">BD</span></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <x-ui.icon name="swatch" class="w-4 h-4 text-neutral-400" />
                            <span class="text-xs font-bold text-neutral-700 dark:text-neutral-300">{{ $property->bathrooms }} <span class="text-[10px] font-medium text-neutral-500">BA</span></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <x-ui.icon name="arrows-pointing-out" class="w-4 h-4 text-neutral-400" />
                            <span class="text-xs font-bold text-neutral-700 dark:text-neutral-300">{{ number_format($property->area_sqft) }} <span class="text-[10px] font-medium text-neutral-500">SQFT</span></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="text-lg font-black text-neutral-900 dark:text-white">
                            {{ $property->formatted_price }}
                        </div>
                        <x-ui.button tag="a" href="{{ route('properties.show', $property->slug) }}" variant="ghost" size="sm" class="text-[10px] font-extrabold uppercase tracking-tighter group/btn">
                            View Details 
                            <x-ui.icon name="arrow-right" class="w-3 h-3 ml-1 transition-transform group-hover/btn:translate-x-1" />
                        </x-ui.button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 bg-white dark:bg-neutral-800 rounded-[--radius-box] border border-dashed border-neutral-200 dark:border-neutral-700 flex flex-col items-center justify-center text-center">
                <div class="w-16 h-16 bg-neutral-50 dark:bg-neutral-900 rounded-full flex items-center justify-center mb-4">
                    <x-ui.icon name="home-modern" class="w-8 h-8 text-neutral-300" />
                </div>
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">No properties match your criteria</h3>
                <p class="text-xs text-neutral-500 mt-1 max-w-xs">Try adjusting your filters or searching for something else.</p>
                <x-ui.button wire:click="resetFilters" variant="outline" size="sm" class="mt-6 text-[10px] font-bold uppercase tracking-widest">
                    Clear All Filters
                </x-ui.button>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($this->properties->hasPages())
        <div class="mt-12 bg-white dark:bg-neutral-800 p-4 rounded-[--radius-box] border border-neutral-100 dark:border-neutral-700">
            {{ $this->properties->links() }}
        </div>
    @endif
</div>
