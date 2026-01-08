<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use App\Models\Brand;
use App\Livewire\Concerns\HasToast;

new #[Layout('layouts.app')] class extends Component {
    use HasToast;

    public Brand $brand;

    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('nullable|string')]
    public string $description = '';

    #[Rule('required|string|max:50')]
    public string $api_code = '';

    #[Rule('boolean')]
    public bool $status = true;

    public function mount(Brand $brand)
    {
        $this->brand = $brand;
        $this->name = $brand->name;
        $this->description = $brand->description ?? '';
        $this->api_code = $brand->api_code;
        $this->status = $brand->status;
    }

    public function save()
    {
        $this->validate([
            'api_code' => 'required|string|max:50|unique:brands,api_code,' . $this->brand->id,
        ]);

        $this->brand->update([
            'name' => $this->name,
            'description' => $this->description,
            'api_code' => $this->api_code,
            'status' => $this->status,
        ]);

        $this->toastSuccess('Brand updated successfully.');
    }

    public function delete()
    {
        $this->brand->delete();
        $this->toastSuccess('Brand deleted successfully.');
        return redirect()->route('admin.brands.index');
    }
}; ?>

<div class="max-w-2xl mx-auto p-6 space-y-6">
    <x-page-header 
        heading="Edit Brand" 
        :description="$brand->name"
    >
        <x-slot:actions>
            <div class="flex items-center gap-3">
                <x-ui.button 
                    type="button" 
                    wire:click="delete" 
                    wire:confirm="Are you sure you want to delete this brand?"
                    variant="danger" 
                    outline
                >
                    Delete
                </x-ui.button>
                <x-ui.button tag="a" href="{{ route('admin.brands.index') }}" variant="outline">
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
                <x-ui.label for="api_code">API Code</x-ui.label>
                <x-ui.input wire:model="api_code" id="api_code" />
                <x-ui.error name="api_code" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label for="description">Description</x-ui.label>
                <x-ui.textarea wire:model="description" id="description" rows="3" />
                <x-ui.error name="description" />
            </x-ui.field>

            <div class="flex items-center gap-2">
                <x-ui.checkbox wire:model="status" id="status" />
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
