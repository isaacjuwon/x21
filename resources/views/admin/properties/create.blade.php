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

    #[Rule('required|string|max:255')]
    public string $title = '';

    #[Rule('nullable|string')]
    public string $description = '';

    #[Rule('required|string')]
    public string $type = '';

    #[Rule('required|string')]
    public string $status = 'available';

    #[Rule('required|string')]
    public string $listing_type = 'sale';

    #[Rule('required|numeric|min:0')]
    public $price = 0;

    #[Rule('required|string|max:255')]
    public string $address = '';

    #[Rule('required|string|max:255')]
    public string $city = '';

    #[Rule('required|string|max:255')]
    public string $state = '';

    #[Rule('required|string|max:255')]
    public string $country = 'Nigeria';

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

    public function save()
    {
        $this->validate();

        Property::create([
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
            'is_active' => true,
        ]);

        $this->toastSuccess('Property created successfully.');

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

<div class="max-w-4xl mx-auto p-6 space-y-6">
    <x-page-header 
        heading="Create Property" 
        description="Add a new real estate listing to the platform"
    >
        <x-slot:actions>
            <x-ui.button tag="a" href="{{ route('admin.properties.index') }}" variant="outline">
                Cancel
            </x-ui.button>
        </x-slot:actions>
    </x-page-header>

    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-6">
                <x-ui.fieldset label="Basic Information">
                    <x-ui.field>
                        <x-ui.label for="title">Title</x-ui.label>
                        <x-ui.input wire:model="title" id="title" placeholder="e.g. Luxury 4 Bedroom Semi-Detached Duplex" />
                        <x-ui.error name="title" />
                    </x-ui.field>

                    <div class="grid grid-cols-2 gap-4">
                        <x-ui.field>
                            <x-ui.label for="type">Property Type</x-ui.label>
                            <x-ui.select wire:model="type" id="type">
                                <x-ui.select.option value="">Select type</x-ui.select.option>
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
            </div>

            <div class="space-y-6">
                <x-ui.fieldset label="Location">
                    <x-ui.field>
                        <x-ui.label for="address">Street Address</x-ui.label>
                        <x-ui.input wire:model="address" id="address" />
                        <x-ui.error name="address" />
                    </x-ui.field>

                    <div class="grid grid-cols-2 gap-4">
                        <x-ui.field>
                            <x-ui.label for="city">City</x-ui.label>
                            <x-ui.input wire:model="city" id="city" />
                            <x-ui.error name="city" />
                        </x-ui.field>
                        <x-ui.field>
                            <x-ui.label for="state">State</x-ui.label>
                            <x-ui.input wire:model="state" id="state" />
                            <x-ui.error name="state" />
                        </x-ui.field>
                    </div>
                </x-ui.fieldset>

                <x-ui.fieldset label="Owner Information">
                    <x-ui.field>
                        <x-ui.label for="owner_name">Name</x-ui.label>
                        <x-ui.input wire:model="owner_name" id="owner_name" />
                        <x-ui.error name="owner_name" />
                    </x-ui.field>
                    <x-ui.field>
                        <x-ui.label for="owner_email">Email</x-ui.label>
                        <x-ui.input wire:model="owner_email" id="owner_email" type="email" />
                        <x-ui.error name="owner_email" />
                    </x-ui.field>
                    <x-ui.field>
                        <x-ui.label for="owner_phone">Phone</x-ui.label>
                        <x-ui.input wire:model="owner_phone" id="owner_phone" />
                        <x-ui.error name="owner_phone" />
                    </x-ui.field>
                </x-ui.fieldset>

                <x-ui.fieldset label="Description">
                    <x-ui.textarea wire:model="description" id="description" rows="4" placeholder="Describe the property details..." />
                    <x-ui.error name="description" />
                </x-ui.fieldset>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t border-neutral-100 dark:border-neutral-700">
            <x-ui.button tag="a" href="{{ route('admin.properties.index') }}" variant="ghost">
                Cancel
            </x-ui.button>
            <x-ui.button type="submit" variant="primary">
                Create Property
            </x-ui.button>
        </div>
    </form>
</div>
