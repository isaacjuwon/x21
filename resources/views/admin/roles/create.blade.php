<?php

use App\Livewire\Concerns\HasToast;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

new class extends Component
{
    use HasToast;

    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('required|string|max:255')]
    public string $guard_name = 'web';

    public array $selectedPermissions = [];

    #[On('show-create-role-modal')]
    public function openModal(): void
    {
        $this->reset();
        $this->guard_name = 'web';
        $this->resetErrorBag();
        $this->dispatch('open-modal', id: 'create-role-modal');
    }

    public function save()
    {
        $this->validate();

        $role = Role::create([
            'name' => $this->name,
            'guard_name' => $this->guard_name,
        ]);

        if (! empty($this->selectedPermissions)) {
            $role->syncPermissions($this->selectedPermissions);
        }

        $this->toastSuccess('Role created successfully.');
        $this->dispatch('close-modal', id: 'create-role-modal');
        $this->dispatch('role-created');
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
    id="create-role-modal"
    heading="Create Role" 
    description="Add a new role with permissions"
    width="lg"
>
    <form wire:submit="save" class="space-y-6">
        <x-ui.field>
            <x-ui.label for="create_role_name">Role Name</x-ui.label>
            <x-ui.input wire:model="name" id="create_role_name" placeholder="e.g., Admin" autofocus />
            <x-ui.error name="name" />
        </x-ui.field>

        <x-ui.field>
            <x-ui.label for="create_role_guard">Guard Name</x-ui.label>
            <x-ui.select wire:model.live="guard_name" id="create_role_guard">
                <x-ui.select.option value="web">Web</x-ui.select.option>
                <x-ui.select.option value="api">API</x-ui.select.option>
            </x-ui.select>
            <x-ui.error name="guard_name" />
        </x-ui.field>

        <div>
            <x-ui.label>Permissions</x-ui.label>
            <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 p-6">
                <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Create Role</h1>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Add a new system role</p>
                <div class="mt-2 max-h-64 overflow-y-auto space-y-2">
                    @forelse($permissions as $permission)
                        <div class="flex items-center gap-2">
                            <input 
                                type="checkbox" 
                                wire:model="selectedPermissions" 
                                value="{{ $permission->name }}" 
                                id="create_perm_{{ $permission->id }}"
                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500"
                            >
                            <label for="create_perm_{{ $permission->id }}" class="text-sm text-neutral-700 dark:text-neutral-300">
                                {{ $permission->name }}
                            </label>
                        </div>
                    @empty
                        <p class="text-sm text-neutral-500">No permissions available for this guard</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4 gap-3">
            <x-ui.button type="button" x-on:click="$dispatch('close-modal', {id: 'create-role-modal'})" variant="outline">
                Cancel
            </x-ui.button>
            <x-ui.button type="submit" variant="primary">
                Create Role
            </x-ui.button>
        </div>
    </form>
</x-ui.modal>
