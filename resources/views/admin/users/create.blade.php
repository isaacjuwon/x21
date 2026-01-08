<?php

use App\Livewire\Concerns\HasToast;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new class extends Component
{
    use HasToast;

    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('required|email|unique:users,email')]
    public string $email = '';

    #[Rule('nullable|string|max:20|unique:users,phone_number')]
    public string $phone_number = '';

    #[Rule('required|string|min:8')]
    public string $password = '';

    public array $selectedRoles = [];

    #[On('show-create-user-modal')]
    public function openModal(): void
    {
        $this->reset();
        $this->resetErrorBag();
        $this->dispatch('open-modal', id: 'create-user-modal');
    }

    public function save()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'password' => $this->password,
        ]);

        if (! empty($this->selectedRoles)) {
            $user->syncRoles($this->selectedRoles);
        }

        $this->toastSuccess('User created successfully.');
        $this->dispatch('close-modal', id: 'create-user-modal');
        $this->dispatch('user-created');
    }

    public function render()
    {
        return $this->view([
            'roles' => Role::where('guard_name', 'web')->get(),
        ])->layout('layouts::admin');
    }
}; ?>

<x-ui.modal 
    id="create-user-modal"
    heading="Create User" 
    description="Add a new user to the platform"
    width="lg"
>
    <form wire:submit="save" class="space-y-6">
        <x-ui.field>
            <x-ui.label for="create_user_name">Name</x-ui.label>
            <x-ui.input wire:model="name" id="create_user_name" placeholder="John Doe" autofocus />
            <x-ui.error name="name" />
        </x-ui.field>

        <x-ui.field>
            <x-ui.label for="create_user_email">Email</x-ui.label>
            <x-ui.input wire:model="email" id="create_user_email" type="email" placeholder="john@example.com" />
            <x-ui.error name="email" />
        </x-ui.field>

        <x-ui.field>
            <x-ui.label for="create_user_phone">Phone Number</x-ui.label>
            <x-ui.input wire:model="phone_number" id="create_user_phone" type="tel" placeholder="+1234567890" />
            <x-ui.error name="phone_number" />
        </x-ui.field>

        <x-ui.field>
            <x-ui.label for="create_user_password">Password</x-ui.label>
            <x-ui.input wire:model="password" id="create_user_password" type="password" placeholder="••••••••" />
            <x-ui.error name="password" />
        </x-ui.field>

        <div>
            <x-ui.label>Roles</x-ui.label>
            <div class="mt-2 max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg p-4 space-y-2">
                @forelse($roles as $role)
                    <div class="flex items-center gap-2">
                        <input 
                            type="checkbox" 
                            wire:model="selectedRoles" 
                            value="{{ $role->name }}" 
                            id="create_user_role_{{ $role->id }}"
                            class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500"
                        >
                        <label for="create_user_role_{{ $role->id }}" class="text-sm text-gray-700 dark:text-gray-300">
                            {{ $role->name }}
                        </label>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No roles available</p>
                @endforelse
            </div>
        </div>

        <div class="flex justify-end pt-4 gap-3">
            <x-ui.button type="button" x-on:click="$dispatch('close-modal', {id: 'create-user-modal'})" variant="outline">
                Cancel
            </x-ui.button>
            <x-ui.button type="submit" variant="primary">
                Create User
            </x-ui.button>
        </div>
    </form>
</x-ui.modal>
