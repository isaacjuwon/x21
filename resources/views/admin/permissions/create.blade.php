<?php

use App\Livewire\Concerns\HasToast;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

new class extends Component
{
    use HasToast;

    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('required|string|max:255')]
    public string $guard_name = 'web';

    #[On('show-create-permission-modal')]
    public function openModal(): void
    {
        $this->reset();
        $this->guard_name = 'web';
        $this->resetErrorBag();
        $this->dispatch('open-modal', id: 'create-permission-modal');
    }

    public function save()
    {
        $this->validate();

        Permission::create([
            'name' => $this->name,
            'guard_name' => $this->guard_name,
        ]);

        $this->toastSuccess('Permission created successfully.');
        $this->dispatch('close-modal', id: 'create-permission-modal');
        $this->dispatch('permission-created');
    }

    public function render()
    {
        return $this->view()->layout('layouts::admin');
    }
}; ?>

<x-ui.modal 
    id="create-permission-modal"
    heading="Create Permission" 
    description="Add a new system permission"
    width="md"
>
    <form wire:submit="save" class="space-y-6">
        <x-ui.field>
            <x-ui.label for="create_permission_name">Permission Name</x-ui.label>
            <x-ui.input wire:model="name" id="create_permission_name" placeholder="e.g., manage users" autofocus />
            <x-ui.error name="name" />
            <p class="text-xs text-neutral-500 mt-1">Use lowercase with spaces or hyphens</p>
        </x-ui.field>

        <x-ui.field>
            <x-ui.label for="create_permission_guard">Guard Name</x-ui.label>
            <x-ui.select wire:model="guard_name" id="create_permission_guard">
                <x-ui.select.option value="web">Web</x-ui.select.option>
                <x-ui.select.option value="api">API</x-ui.select.option>
            </x-ui.select>
            <x-ui.error name="guard_name" />
        </x-ui.field>

        <div class="flex justify-end pt-4 gap-3">
            <x-ui.button type="button" x-on:click="$dispatch('close-modal', {id: 'create-permission-modal'})" variant="outline">
                Cancel
            </x-ui.button>
            <x-ui.button type="submit" variant="primary">
                Create Permission
            </x-ui.button>
        </div>
    </form>
</x-ui.modal>
