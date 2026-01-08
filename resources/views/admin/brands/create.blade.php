<?php

use App\Enums\Media\MediaCollectionType;
use App\Livewire\Concerns\HasToast;
use App\Models\Brand;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use HasToast;
    use WithFileUploads;

    #[Rule('nullable|image|max:1024')] // 1MB Max
    public $image;

    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('nullable|string')]
    public string $description = '';

    #[Rule('boolean')]
    public bool $status = true;

    public function openModal(): void
    {
        $this->reset();
        $this->resetErrorBag();
        $this->image = null;
        $this->dispatch('open-modal', id: 'create-brand-modal');
    }

    public function save()
    {
        $this->validate();

        $brand = Brand::create([
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
        ]);

        if ($this->image) {
            $brand->addMedia($this->image)
                ->toMediaCollection(MediaCollectionType::Brand->value);
        }

        $this->toastSuccess('Brand created successfully.');
        $this->dispatch('close-modal', id: 'create-brand');
        $this->dispatch('refresh-brand-list');
    }

    public function render()
    {
        return $this->view()
            ->title('Create Brand')
            ->layout('layouts::admin');
    }
}; ?>

<x-ui.modal 
    id="create-brand"
    heading="Create Brand" 
    description="Add a new service brand"
    width="md"
>
    <form wire:submit.prevent="save" class="space-y-6">
        <x-ui.field>
            <x-ui.label for="create_name">Name</x-ui.label>
            <x-ui.input wire:model="name" id="create_name" placeholder="e.g. MTN" autofocus />
            <x-ui.error name="name" />
        </x-ui.field>

        <x-ui.field>
            <x-ui.label for="create_image">Logo</x-ui.label>
            <x-ui.input type="file" wire:model="image" id="create_image" />
            <x-ui.error name="image" />
        </x-ui.field>


        <x-ui.field>
            <x-ui.label for="create_description">Description</x-ui.label>
            <x-ui.textarea wire:model="description" id="create_description" rows="3" placeholder="Optional description..." />
            <x-ui.error name="description" />
        </x-ui.field>

        <div class="flex items-center gap-2">
            <x-ui.checkbox wire:model="status" id="create_status" />
            <x-ui.label for="create_status" class="mb-0">Active</x-ui.label>
        </div>

        <div class="flex justify-end pt-4 gap-3">
            <x-ui.button type="button" x-on:click="$dispatch('close-modal', {id: 'create-brand-modal'})" variant="outline">
                Cancel
            </x-ui.button>
            <x-ui.button type="submit" variant="primary">
                Create Brand
            </x-ui.button>
        </div>
    </form>
</x-ui.modal>
