<?php

use App\Enums\Media\MediaCollectionType;
use App\Livewire\Concerns\HasToast;
use App\Models\Brand;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use HasToast;
    use WithFileUploads;

    #[Locked]
    public ?Brand $brand = null;

    #[Rule('nullable|image|max:1024')] // 1MB Max
    public $image;

    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('nullable|string')]
    public string $description = '';

    #[Rule('boolean')]
    public bool $status = true;

    #[On('show-view-brand')]
    public function setBrand(string $id): void
    {
        $brand = Brand::find($id);

        if (! $brand) {
            return;
        }

        $this->brand = $brand;
        $this->name = $brand->name;
        $this->description = $brand->description ?? '';
        $this->status = $brand->status;
        $this->image = null;

        $this->dispatch('open-modal', id: 'view-brand');
    }

    public function save()
    {

        $this->brand->update([
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
        ]);

        if ($this->image) {
            $this->brand->addMedia($this->image)
                ->toMediaCollection(MediaCollectionType::Brand->value);
        }

        $this->toastSuccess('Brand updated successfully.');
        $this->dispatch('close-modal', id: 'view-brand-modal');
        $this->dispatch('refresh-brand-list');
    }

    public function delete()
    {
        $this->brand->delete();
        $this->toastSuccess('Brand deleted successfully.');
        $this->dispatch('close-modal', id: 'view-brand-modal');
        $this->dispatch('refresh-brand-list');
    }

    public function render()
    {
        return $this->view()
            ->title('View Brand')
            ->layout('layouts::admin');
    }
}; ?>

<x-ui.modal 
    id="view-brand"
    heading="Edit Brand" 
    :description="$brand?->name"
    width="md"
>
    @if($brand)
        <form wire:submit="save" class="space-y-6">
            <x-ui.field>
                <x-ui.label for="view_name">Name</x-ui.label>
                <x-ui.input wire:model="name" id="view_name" />
                <x-ui.error name="name" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label for="view_image">Logo</x-ui.label>
                @if($brand->image_url)
                    <div class="mb-2">
                        <img src="{{ $brand->image_url }}" alt="{{ $brand->name }}" class="h-16 w-16 object-contain rounded-[--radius-field] border border-neutral-200 dark:border-neutral-700">
                    </div>
                @endif
                <x-ui.input type="file" wire:model="image" id="view_image" />
                <x-ui.error name="image" />
            </x-ui.field>


            <x-ui.field>
                <x-ui.label for="view_description">Description</x-ui.label>
                <x-ui.textarea wire:model="description" id="view_description" rows="3" />
                <x-ui.error name="description" />
            </x-ui.field>

            <div class="flex items-center gap-2">
                <x-ui.checkbox wire:model="status" id="view_status" class="rounded-[--radius-field]" />
                <x-ui.label for="view_status" class="mb-0 text-xs font-bold">Active</x-ui.label>
            </div>

            <div class="flex justify-between pt-4">
                <x-ui.button 
                    type="button" 
                    wire:click="delete" 
                    wire:confirm="Are you sure you want to delete this brand?"
                    variant="danger" 
                    outline
                >
                    Delete
                </x-ui.button>
                <div class="flex gap-3">
                    <x-ui.button type="button" x-on:click="$dispatch('close-modal', {id: 'view-brand-modal'})" variant="outline">
                        Cancel
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary">
                        Save Changes
                    </x-ui.button>
                </div>
            </div>
        </form>
    @endif
</x-ui.modal>
