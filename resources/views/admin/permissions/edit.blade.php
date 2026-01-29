<?php

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Spatie\Permission\Models\Permission;
use App\Livewire\Concerns\HasToast;

new class extends Component {
    use HasToast;

    public ?Permission $permission = null;

    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('required|string|max:255')]
    public string $guard_name = 'web';

    #[On('show-edit-permission-modal')]
    public function openModal($permissionId): void
    {
        $this->permission = Permission::findOrFail($permissionId);
        $this->name = $this->permission->name;
        $this->guard_name = $this->permission->guard_name;
        $this->resetErrorBag();
        $this->dispatch('open-modal', id: 'edit-permission-modal');
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $this->permission->id,
            'guard_name' => 'required|string|max:255',
        ]);

        $this->permission->update([
            'name' => $this->name,
            'guard_name' => $this->guard_name,
        ]);

        $this->toastSuccess('Permission updated successfully.');
        $this->dispatch('close-modal', id: 'edit-permission-modal');
        $this->dispatch('permission-updated');
    }

    public function delete()
    {
        $this->permission->delete();
        $this->toastSuccess('Permission deleted successfully.');
        $this->dispatch('close-modal', id: 'edit-permission-modal');
        $this->dispatch('permission-deleted');
    }

    public function render()
    {
        return $this->view()->layout('layouts::admin');
    }
}; ?>

<x-ui.modal 
    id="edit-permission-modal"
    heading="Edit Permission" 
    description="Update permission information"
    width="md"
>
    @if($permission)
        <form wire:submit="save" class="space-y-6">
            <x-ui.field>
                <x-ui.label for="edit_permission_name">Permission Name</x-ui.label>
                <x-ui.input wire:model="name" id="edit_permission_name" />
                <x-ui.error name="name" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label for="edit_permission_guard">Guard Name</x-ui.label>
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Edit Permission</h1>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Update system permission details</p>
                <x-ui.select wire:model="guard_name" id="edit_permission_guard">
                    <x-ui.select.option value="web">Web</x-ui.select.option>
                    <x-ui.select.option value="api">API</x-ui.select.option>
                </x-ui.select>
                <x-ui.error name="guard_name" />
            </x-ui.field>

            <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 p-6">
                <x-ui.button 
                    type="button" 
                    wire:click="delete" 
                    wire:confirm="Are you sure you want to delete this permission?"
                    variant="danger" 
                    outline
                >
                    Delete
                </x-ui.button>
                <div class="flex gap-3">
                    <x-ui.button type="button" x-on:click="$dispatch('close-modal', {id: 'edit-permission-modal'})" variant="outline">
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
