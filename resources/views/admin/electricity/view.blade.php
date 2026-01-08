<?php

use App\Livewire\Concerns\HasToast;
use App\Models\Brand;
use App\Models\ElectricityPlan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

new class extends Component
{
    use HasToast;

    public ElectricityPlan $plan;

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

    #[Rule('boolean')]
    public bool $status = false;

    public function mount(ElectricityPlan $plan)
    {
        $this->plan = $plan;
        $this->name = $plan->name;
        $this->description = $plan->description;
        $this->brand_id = $plan->brand_id;
        $this->api_code = $plan->api_code;
        $this->service_id = $plan->service_id;
        $this->status = $plan->status;
    }

    public function save()
    {
        $this->validate();

        $this->plan->update([
            'name' => $this->name,
            'description' => $this->description,
            'brand_id' => $this->brand_id,
            'api_code' => $this->api_code,
            'service_id' => $this->service_id,
            'status' => $this->status,
        ]);

        $this->toastSuccess('Electricity plan updated successfully.');
    }

    public function delete()
    {
        $this->plan->delete();
        $this->toastSuccess('Electricity plan deleted successfully.');

        return redirect()->route('admin.electricity.index');
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
        heading="Edit Electricity Plan" 
        :description="$plan->name"
    >
        <x-slot:actions>
            <div class="flex items-center gap-3">
                <x-ui.button 
                    type="button" 
                    wire:click="delete" 
                    wire:confirm="Are you sure you want to delete this plan?"
                    variant="danger" 
                    outline
                >
                    Delete
                </x-ui.button>
                <x-ui.button tag="a" href="{{ route('admin.electricity.index') }}" variant="outline">
                    Back
                </x-ui.button>
            </div>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <form wire:submit="save" class="space-y-6">
            <x-ui.field>
                <x-ui.label for="name">Name</x-ui.label>
                <x-ui.input wire:model="name" id="name" />
                <x-ui.error name="name" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label for="description">Description</x-ui.label>
                <x-ui.input wire:model="description" id="description" />
                <x-ui.error name="description" />
            </x-ui.field>

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
                <x-ui.label for="api_code">API Code (External)</x-ui.label>
                <x-ui.input wire:model="api_code" id="api_code" />
                <x-ui.error name="api_code" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label for="service_id">Service ID</x-ui.label>
                <x-ui.input wire:model="service_id" id="service_id" />
                <x-ui.error name="service_id" />
            </x-ui.field>

            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model="status" id="status" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500">
                <x-ui.label for="status" class="mb-0">Active</x-ui.label>
            </div>

            <div class="flex justify-end pt-4">
                <x-ui.button type="submit" variant="primary">
                    Save Changes
                </x-ui.button>
            </div>
        </form>
    </div>
</div>
