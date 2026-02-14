<?php

namespace App\Livewire\Admin\Properties;

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
    public ?string $status = null;
    public ?string $type = null;
    public ?string $listing_type = null;

    #[Computed]
    public function properties()
    {
        return Property::query()
            ->when($this->search, function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('city', 'like', '%' . $this->search . '%')
                  ->orWhere('address', 'like', '%' . $this->search . '%');
            })
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->listing_type, fn ($q) => $q->where('listing_type', $this->listing_type))
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function statuses()
    {
        return PropertyStatus::cases();
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

    public function delete(Property $property)
    {
        $property->delete();
        $this->dispatch('property-deleted');
    }

    public function render()
    {
        return $this->view()->layout('layouts::admin');
    }
}; ?>

<div class="max-w-7xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white">Properties</h1>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Manage real estate listings</p>
        </div>
        <x-ui.button icon="plus" tag="a" href="{{ route('admin.properties.create') }}" variant="primary">
            Add Property
        </x-ui.button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="md:col-span-1">
            <x-ui.input 
                wire:model.live.debounce.300ms="search" 
                placeholder="Search properties..." 
                type="search"
            >
                <x-slot:leading>
                    <x-ui.icon name="magnifying-glass" class="w-5 h-5 text-gray-400" />
                </x-slot:leading>
            </x-ui.input>
        </div>
        <div>
            <x-ui.select wire:model.live="status">
                <x-ui.select.option value="">All Statuses</x-ui.select.option>
                @foreach($this->statuses as $s)
                    <x-ui.select.option value="{{ $s->value }}">{{ $s->getLabel() }}</x-ui.select.option>
                @endforeach
            </x-ui.select>
        </div>
        <div>
            <x-ui.select wire:model.live="type">
                <x-ui.select.option value="">All Types</x-ui.select.option>
                @foreach($this->types as $t)
                    <x-ui.select.option value="{{ $t->value }}">{{ $t->getLabel() }}</x-ui.select.option>
                @endforeach
            </x-ui.select>
        </div>
        <div>
            <x-ui.select wire:model.live="listing_type">
                <x-ui.select.option value="">All Listing Types</x-ui.select.option>
                @foreach($this->listingTypes as $lt)
                    <x-ui.select.option value="{{ $lt->value }}">{{ $lt->getLabel() }}</x-ui.select.option>
                @endforeach
            </x-ui.select>
        </div>
    </div>

    <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-neutral-50 dark:bg-neutral-700/50 text-neutral-500 dark:text-neutral-400 font-bold">
                    <tr>
                        <th class="px-6 py-4">Property</th>
                        <th class="px-6 py-4">Location</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Price</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                    @forelse($this->properties as $property)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($property->main_image)
                                        <img src="{{ $property->main_image }}" class="w-10 h-10 rounded-[--radius-field] object-cover" alt="">
                                    @else
                                        <div class="w-10 h-10 rounded-[--radius-field] bg-neutral-100 dark:bg-neutral-700 flex items-center justify-center">
                                            <x-ui.icon name="photo" class="w-5 h-5 text-neutral-400" />
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-bold text-neutral-900 dark:text-white">{{ $property->title }}</p>
                                        <p class="text-[10px] text-neutral-500">{{ $property->listing_type->getLabel() }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-neutral-500 dark:text-neutral-400">
                                {{ $property->city }}, {{ $property->state }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="flex items-center gap-1.5">
                                    <x-ui.icon name="home" class="w-3.5 h-3.5" />
                                    {{ $property->type->getLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-bold text-neutral-900 dark:text-white">
                                {{ $property->formatted_price }}
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :color="$property->status->getColor()">
                                    {{ $property->status->getLabel() }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <x-ui.button tag="a" href="{{ route('admin.properties.view', $property) }}" variant="ghost" size="sm">
                                    Edit
                                </x-ui.button>
                                <x-ui.button wire:click="delete({{ $property->id }})" wire:confirm="Are you sure?" variant="ghost" size="sm" class="text-red-600">
                                    Delete
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400">
                                <div class="flex flex-col items-center justify-center">
                                    <x-ui.icon name="inbox" class="w-12 h-12 text-gray-300 mb-3" />
                                    <p>No properties found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-neutral-100 dark:border-neutral-700">
            {{ $this->properties->links() }}
        </div>
    </div>
</div>
