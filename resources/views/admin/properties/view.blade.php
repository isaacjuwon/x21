<?php

namespace App\Livewire\Admin\Properties;

use App\Enums\PropertyListingType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Livewire\Concerns\HasToast;
use App\Models\Property;
use Livewire\Attributes\Rule;
use Livewire\Component;

new class extends Component
{
    use HasToast;

    public Property $property;

    #[Rule('required|string|max:255')]
    public string $title = '';

    #[Rule('nullable|string')]
    public string $description = '';

    #[Rule('required|string')]
    public string $type = '';

    #[Rule('required|string')]
    public string $status = '';

    #[Rule('required|string')]
    public string $listing_type = '';

    #[Rule('required|numeric|min:0')]
    public $price = 0;

    #[Rule('required|string|max:255')]
    public string $address = '';

    #[Rule('required|string|max:255')]
    public string $city = '';

    #[Rule('required|string|max:255')]
    public string $state = '';

    #[Rule('required|string|max:255')]
    public string $country = '';

    #[Rule('nullable|integer|min:0')]
    public int $bedrooms = 0;

    #[Rule('nullable|integer|min:0')]
    public int $bathrooms = 0;

    #[Rule('nullable|integer|min:0')]
    public int $area_sqft = 0;

    #[Rule('nullable|integer')]
    public ?int $year_built = null;

    public bool $has_garage = false;
    public bool $is_furnished = false;
    public int $parking_spaces = 0;

    #[Rule('nullable|string|max:255')]
    public string $owner_name = '';

    #[Rule('nullable|email|max:255')]
    public string $owner_email = '';

    #[Rule('nullable|string|max:255')]
    public string $owner_phone = '';

    public function mount(Property $property)
    {
        $this->property = $property;
        $this->title = $property->title;
        $this->description = $property->description ?? '';
        $this->type = $property->type->value;
        $this->status = $property->status->value;
        $this->listing_type = $property->listing_type->value;
        $this->price = $property->price;
        $this->address = $property->address;
        $this->city = $property->city;
        $this->state = $property->state;
        $this->country = $property->country;
        $this->bedrooms = $property->bedrooms;
        $this->bathrooms = $property->bathrooms;
        $this->area_sqft = $property->area_sqft;
        $this->year_built = $property->year_built;
        $this->has_garage = $property->has_garage;
        $this->is_furnished = $property->is_furnished;
        $this->parking_spaces = $property->parking_spaces;
        $this->owner_name = $property->owner_name ?? '';
        $this->owner_email = $property->owner_email ?? '';
        $this->owner_phone = $property->owner_phone ?? '';
    }

    public function save()
    {
        $this->validate();

        $this->property->update([
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'status' => $this->status,
            'listing_type' => $this->listing_type,
            'price' => $this->price,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'area_sqft' => $this->area_sqft,
            'year_built' => $this->year_built,
            'has_garage' => $this->has_garage,
            'is_furnished' => $this->is_furnished,
            'parking_spaces' => $this->parking_spaces,
            'owner_name' => $this->owner_name,
            'owner_email' => $this->owner_email,
            'owner_phone' => $this->owner_phone,
        ]);

        $this->toastSuccess('Property updated successfully.');
    }

    public function delete()
    {
        $this->property->delete();
        $this->toastSuccess('Property deleted successfully.');
        return redirect()->route('admin.properties.index');
    }

    public function render()
    {
        return $this->view()->with([
            'types' => PropertyType::cases(),
            'statuses' => PropertyStatus::cases(),
            'listingTypes' => PropertyListingType::cases(),
        ])->layout('layouts::admin');
    }
}; ?>

<div class="max-w-7xl mx-auto p-6 space-y-8">
    <x-page-header 
        :heading="'Edit Property: ' . $property->title" 
        :description="'Manage and update real estate listing details'"
    >
        <x-slot:actions>
            <div class="flex items-center gap-3">
                <x-ui.button 
                    type="button" 
                    wire:click="delete" 
                    wire:confirm="Are you sure you want to delete this property?"
                    variant="ghost" 
                    class="text-red-600 hover:text-red-700"
                >
                    <x-ui.icon name="trash" class="w-4 h-4 mr-2" />
                    Delete
                </x-ui.button>
                <x-ui.button tag="a" href="{{ route('admin.properties.index') }}" variant="outline">
                    Back to List
                </x-ui.button>
            </div>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left: Main Form --}}
        <div class="lg:col-span-2">
            <form wire:submit="save" class="space-y-6">
                <x-ui.fieldset label="Basic Information">
                    <x-ui.field>
                        <x-ui.label for="title">Title</x-ui.label>
                        <x-ui.input wire:model="title" id="title" />
                        <x-ui.error name="title" />
                    </x-ui.field>

                    <div class="grid grid-cols-2 gap-4">
                        <x-ui.field>
                            <x-ui.label for="type">Property Type</x-ui.label>
                            <x-ui.select wire:model="type" id="type">
                                @foreach($types as $t)
                                    <x-ui.select.option value="{{ $t->value }}">{{ $t->getLabel() }}</x-ui.select.option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.error name="type" />
                        </x-ui.field>

                        <x-ui.field>
                            <x-ui.label for="listing_type">Listing Type</x-ui.label>
                            <x-ui.select wire:model="listing_type" id="listing_type">
                                @foreach($listingTypes as $lt)
                                    <x-ui.select.option value="{{ $lt->value }}">{{ $lt->getLabel() }}</x-ui.select.option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.error name="listing_type" />
                        </x-ui.field>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <x-ui.field>
                            <x-ui.label for="price">Price (NGN)</x-ui.label>
                            <x-ui.input wire:model="price" id="price" type="number" step="0.01" />
                            <x-ui.error name="price" />
                        </x-ui.field>

                        <x-ui.field>
                            <x-ui.label for="status">Status</x-ui.label>
                            <x-ui.select wire:model="status" id="status">
                                @foreach($statuses as $s)
                                    <x-ui.select.option value="{{ $s->value }}">{{ $s->getLabel() }}</x-ui.select.option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.error name="status" />
                        </x-ui.field>
                    </div>
                </x-ui.fieldset>

                <x-ui.fieldset label="Property Features">
                    <div class="grid grid-cols-3 gap-4">
                        <x-ui.field>
                            <x-ui.label for="bedrooms">Bedrooms</x-ui.label>
                            <x-ui.input wire:model="bedrooms" id="bedrooms" type="number" />
                            <x-ui.error name="bedrooms" />
                        </x-ui.field>
                        <x-ui.field>
                            <x-ui.label for="bathrooms">Bathrooms</x-ui.label>
                            <x-ui.input wire:model="bathrooms" id="bathrooms" type="number" />
                            <x-ui.error name="bathrooms" />
                        </x-ui.field>
                        <x-ui.field>
                            <x-ui.label for="area_sqft">Area (sqft)</x-ui.label>
                            <x-ui.input wire:model="area_sqft" id="area_sqft" type="number" />
                            <x-ui.error name="area_sqft" />
                        </x-ui.field>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div class="flex items-center gap-2">
                            <x-ui.checkbox wire:model="has_garage" id="has_garage" label="Has Garage" />
                        </div>
                        <div class="flex items-center gap-2">
                            <x-ui.checkbox wire:model="is_furnished" id="is_furnished" label="Is Furnished" />
                        </div>
                    </div>
                </x-ui.fieldset>

                <x-ui.fieldset label="Detailed Description">
                    <x-ui.textarea wire:model="description" id="description" rows="6" />
                    <x-ui.error name="description" />
                </x-ui.fieldset>

                <div class="flex justify-end pt-4">
                    <x-ui.button type="submit" variant="primary" class="w-full md:w-auto">
                        Update Property Details
                    </x-ui.button>
                </div>
            </form>
        </div>

        {{-- Right: Side Information --}}
        <div class="space-y-8">
            <x-ui.fieldset label="Location Details">
                <x-ui.field>
                    <x-ui.label for="address">Street Address</x-ui.label>
                    <x-ui.input wire:model="address" id="address" class="text-xs" />
                    <x-ui.error name="address" />
                </x-ui.field>

                <div class="grid grid-cols-2 gap-4">
                    <x-ui.field>
                        <x-ui.label for="city">City</x-ui.label>
                        <x-ui.input wire:model="city" id="city" class="text-xs" />
                        <x-ui.error name="city" />
                    </x-ui.field>
                    <x-ui.field>
                        <x-ui.label for="state">State</x-ui.label>
                        <x-ui.input wire:model="state" id="state" class="text-xs" />
                        <x-ui.error name="state" />
                    </x-ui.field>
                </div>
                
                <x-ui.field>
                    <x-ui.label for="country">Country</x-ui.label>
                    <x-ui.input wire:model="country" id="country" class="text-xs" />
                    <x-ui.error name="country" />
                </x-ui.field>
            </x-ui.fieldset>

            <x-ui.fieldset label="Owner Contact">
                <x-ui.field>
                    <x-ui.label for="owner_name">Contact Name</x-ui.label>
                    <x-ui.input wire:model="owner_name" id="owner_name" class="text-xs" />
                    <x-ui.error name="owner_name" />
                </x-ui.field>
                <x-ui.field>
                    <x-ui.label for="owner_email">Email Address</x-ui.label>
                    <x-ui.input wire:model="owner_email" id="owner_email" type="email" class="text-xs" />
                    <x-ui.error name="owner_email" />
                </x-ui.field>
                <x-ui.field>
                    <x-ui.label for="owner_phone">Phone Number</x-ui.label>
                    <x-ui.input wire:model="owner_phone" id="owner_phone" class="text-xs" />
                    <x-ui.error name="owner_phone" />
                </x-ui.field>
            </x-ui.fieldset>

            <div class="bg-neutral-50 dark:bg-neutral-800/50 rounded-[--radius-box] p-6 border border-neutral-100 dark:border-neutral-100/10">
                <h3 class="text-xs font-bold text-neutral-400 uppercase tracking-wider mb-4">Metadata</h3>
                <div class="space-y-3">
                    <div class="flex justify-between text-xs">
                        <span class="text-neutral-500">Created At</span>
                        <span class="text-neutral-900 dark:text-white font-bold">{{ $property->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-neutral-500">Last Updated</span>
                        <span class="text-neutral-900 dark:text-white font-bold">{{ $property->updated_at->diffForHumans() }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-neutral-500">Slug</span>
                        <span class="text-neutral-900 dark:text-white font-mono break-all">{{ $property->slug }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
