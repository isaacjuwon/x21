<?php

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Livewire\Concerns\HasToast;

new class extends Component {
    use HasToast;

    public ?Role $role = null;

    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('required|string|max:255')]
    public string $guard_name = 'web';

    public array $selectedPermissions = [];

    #[On('show-edit-role-modal')]
    public function openModal($roleId): void
    {
        $this->role = Role::findOrFail($roleId);
        $this->name = $this->role->name;
        $this->guard_name = $this->role->guard_name;
        $this->selectedPermissions = $this->role->permissions->pluck('name')->toArray();
        $this->resetErrorBag();
        $this->dispatch('open-modal', id: 'edit-role-modal');
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $this->role->id,
            'guard_name' => 'required|string|max:255',
        ]);

        $this->role->update([
            'name' => $this->name,
            'guard_name' => $this->guard_name,
        ]);
        
        $this->role->syncPermissions($this->selectedPermissions);

        $this->toastSuccess('Role updated successfully.');
        $this->dispatch('close-modal', id: 'edit-role-modal');
        $this->dispatch('role-updated');
    }

    public function delete()
    {
        $this->role->delete();
        $this->toastSuccess('Role deleted successfully.');
        $this->dispatch('close-modal', id: 'edit-role-modal');
        $this->dispatch('role-deleted');
    }

        return [
            'permissions' => Permission::where('guard_name', $this->guard_name)->get(),
        ];
    }

    public function render()
    {
        return $this->view()->layout('layouts::admin');
    }
}; ?>

<x-ui.modal 
    id="edit-role-modal"
    heading="Edit Role" 
    description="Update role information and permissions"
    width="lg"
>
    @if($role)
        <form wire:submit="save" class="space-y-6">
            <x-ui.field>
                <x-ui.label for="edit_role_name">Role Name</x-ui.label>
                <x-ui.input wire:model="name" id="edit_role_name" />
                <x-ui.error name="name" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label for="edit_role_guard">Guard Name</x-ui.label>
                <x-ui.select wire:model.live="guard_name" id="edit_role_guard">
                    <x-ui.select.option value="web">Web</x-ui.select.option>
                    <x-ui.select.option value="api">API</x-ui.select.option>
                </x-ui.select>
                <x-ui.error name="guard_name" />
            </x-ui.field>

            <div>
                <x-ui.label>Permissions</x-ui.label>
                <div class="mt-2 max-h-64 overflow-y-auto border border-neutral-200 dark:border-neutral-700 rounded-[--radius-field] p-4 space-y-2">
                    @forelse($permissions as $permission)
                        <div class="flex items-center gap-2">
                            <input 
                                type="checkbox" 
                                wire:model="selectedPermissions" 
                                value="{{ $permission->name }}" 
                                id="edit_perm_{{ $permission->id }}"
                                class="rounded-[--radius-field] border-neutral-300 text-primary-600 shadow-sm focus:ring-primary-500"
                            >
                            <label for="edit_perm_{{ $permission->id }}" class="text-xs text-neutral-700 dark:text-neutral-300">
                                {{ $permission->name }}
                            </label>
                        </div>
                    @empty
                        <p class="text-xs text-neutral-500">No permissions available for this guard</p>
                    @endforelse
                </div>
            </div>

            <div class="flex justify-between items-center pt-4">
                <x-ui.button 
                    type="button" 
                    wire:click="delete" 
                    wire:confirm="Are you sure you want to delete this role?"
                    variant="danger" 
                    outline
                >
                    Delete
                </x-ui.button>
                <div class="flex gap-3">
                    <x-ui.button type="button" x-on:click="$dispatch('close-modal', {id: 'edit-role-modal'})" variant="outline">
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
