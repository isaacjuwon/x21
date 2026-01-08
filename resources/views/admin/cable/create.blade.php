<?php

use App\Livewire\Concerns\HasToast;
use App\Models\Brand;
use App\Models\CablePlan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

new class extends Component
{
    use HasToast;

    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('nullable|string|max:160')]
    public string $description = '';

    #[Rule('required|integer')]
    public int $brand_id = 0;

    #[Rule('nullable|string|max:255')]
    public string $api_code = '';

    #[Rule('nullable|string|max:255')]
    public string $service_id = '';

    #[Rule('nullable|string|max:255')]
    public string $reference = '';

    #[Rule('nullable|string|max:255')]
    public string $type = '';

    #[Rule('nullable|string|max:255')]
    public string $duration = '';

    #[Rule('required|numeric|min:0')]
    public float $price = 0.0;

    #[Rule('nullable|numeric|min:0')]
    public float $discounted_price = 0.0;

    #[Rule('boolean')]
    public bool $status = false;

    public function save()
    {
        $this->validate();

        CablePlan::create([
            'name' => $this->name,
            'description' => $this->description,
            'brand_id' => $this->brand_id,
            'api_code' => $this->api_code,
            'service_id' => $this->service_id,
            'reference' => $this->reference,
            'type' => $this->type,
            'duration' => $this->duration,
            'price' => $this->price,
            'discounted_price' => $this->discounted_price,
            'status' => $this->status,
        ]);

        $this->toastSuccess('Cable plan created successfully.');

        return redirect()->route('admin.cable.index');
    }

    public function render()
    {
        return $this->view()
            ->with('brands', Brand::active()->get())
            ->layout('layouts::admin');
    }
}; ?>

<div class="max-w-2xl mx-auto p-6 space-y-6">
    <x-page-header 
        heading="Create Cable Plan" 
        description="Add a new cable TV package"
    >
        <x-slot:actions>
            <x-ui.button tag="a" href="{{ route('admin.cable.index') }}" variant="outline">
                Cancel
            </x-ui.button>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <form wire:submit="save" class="space-y-6">
            <x-ui.field>
                <x-ui.label for="name">Name</x-ui.label>
                <x-ui.input wire:model="name" id="name" placeholder="e.g., DSTV Premium" />
                <x-ui.error name="name" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label for="description">Description</x-ui.label>
                <x-ui.input wire:model="description" id="description" placeholder="Brief description" />
                <x-ui.error name="description" />
            </x-ui.field>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-ui.field>
                    <x-ui.label for="brand_id">Provider</x-ui.label>
                    <x-ui.select wire:model="brand_id" id="brand_id">
                        <x-ui.select.option value="">Select Network</x-ui.select.option>
                    @foreach($brands as $brand)
                        <x-ui.select.option value="{{ $brand->id }}">{{ $brand->name }}</x-ui.select.option>
                    @endforeach
                    </x-ui.select>
                    <x-ui.error name="brand_id" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="price">Price</x-ui.label>
                    <x-ui.input wire:model="price" id="price" type="number" step="0.01" />
                    <x-ui.error name="price" />
                </x-ui.field>
            </div>

            <x-ui.field>
                <x-ui.label for="discounted_price">Discounted Price</x-ui.label>
                <x-ui.input wire:model="discounted_price" id="discounted_price" type="number" step="0.01" />
                <x-ui.error name="discounted_price" />
            </x-ui.field>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-ui.field>
                    <x-ui.label for="type">Type</x-ui.label>
                    <x-ui.input wire:model="type" id="type" placeholder="e.g., Monthly, Yearly" />
                    <x-ui.error name="type" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="duration">Duration</x-ui.label>
                    <x-ui.input wire:model="duration" id="duration" placeholder="e.g., 30 days" />
                    <x-ui.error name="duration" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="reference">Reference</x-ui.label>
                    <x-ui.input wire:model="reference" id="reference" placeholder="Internal reference" />
                    <x-ui.error name="reference" />
                </x-ui.field>
            </div>

            <x-ui.field>
                <x-ui.label for="api_code">API Code (External)</x-ui.label>
                <x-ui.input wire:model="api_code" id="api_code" placeholder="e.g., dstv-premium" />
                <x-ui.error name="api_code" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label for="service_id">Service ID</x-ui.label>
                <x-ui.input wire:model="service_id" id="service_id" placeholder="External service identifier" />
                <x-ui.error name="service_id" />
            </x-ui.field>

            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model="status" id="status" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                <x-ui.label for="status" class="mb-0">Active</x-ui.label>
            </div>

            <div class="flex justify-end pt-4">
                <x-ui.button type="submit" variant="primary">
                    Create Plan
                </x-ui.button>
            </div>
        </form>
    </div>
</div>
