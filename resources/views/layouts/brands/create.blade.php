<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use App\Models\Brand;
use App\Livewire\Concerns\HasToast;

new #[Layout('layouts.app')] class extends Component {
    use HasToast;

    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('nullable|string')]
    public string $description = '';

    #[Rule('required|string|max:50|unique:brands,api_code')]
    public string $api_code = '';

    #[Rule('boolean')]
    public bool $status = true;

    public function save()
    {
        $this->validate();

        Brand::create([
            'name' => $this->name,
            'description' => $this->description,
            'api_code' => $this->api_code,
            'status' => $this->status,
        ]);

        $this->toastSuccess('Brand created successfully.');

        return redirect()->route('admin.brands.index');
    }
}; ?>

<div class="max-w-2xl mx-auto p-6 space-y-6">
    <x-page-header 
        heading="Create Brand" 
        description="Add a new service brand"
    >
        <x-slot:actions>
            <x-ui.button tag="a" href="{{ route('admin.brands.index') }}" variant="outline">
                Cancel
            </x-ui.button>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <form wire:submit="save" class="space-y-6">
            <x-ui.field>
                <x-ui.label for="name">Name</x-ui.label>
                <x-ui.input wire:model="name" id="name" placeholder="e.g. MTN" autofocus />
                <x-ui.error name="name" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label for="api_code">API Code</x-ui.label>
                <x-ui.input wire:model="api_code" id="api_code" placeholder="e.g. mtn-data" />
                <x-ui.error name="api_code" />
                <p class="text-xs text-gray-500 mt-1">Unique identifier for API integrations.</p>
            </x-ui.field>

            <x-ui.field>
                <x-ui.label for="description">Description</x-ui.label>
                <x-ui.textarea wire:model="description" id="description" rows="3" placeholder="Optional description..." />
                <x-ui.error name="description" />
            </x-ui.field>

            <div class="flex items-center gap-2">
                <x-ui.checkbox wire:model="status" id="status" />
                <x-ui.label for="status" class="mb-0">Active</x-ui.label>
            </div>

            <div class="flex justify-end pt-4">
                <x-ui.button type="submit" variant="primary">
                    Create Brand
                </x-ui.button>
            </div>
        </form>
    </div>
</div>
