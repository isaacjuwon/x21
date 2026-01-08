<?php

use App\Livewire\Concerns\HasToast;
use App\Models\LoanLevel;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new class extends Component
{
    use HasToast;

    public ?User $user = null;

    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('required|email')]
    public string $email = '';

    #[Rule('nullable|string|max:20')]
    public string $phone_number = '';

    #[Rule('nullable|string|min:8')]
    public string $password = '';

    public ?int $loan_level_id = null;

    public array $selectedRoles = [];

    #[On('show-edit-user-modal')]
    public function openModal($userId): void
    {
        $this->user = User::findOrFail($userId);
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->phone_number = $this->user->phone_number ?? '';
        $this->password = '';
        $this->loan_level_id = $this->user->loan_level_id;
        $this->selectedRoles = $this->user->roles->pluck('name')->toArray();
        $this->resetErrorBag();
        $this->dispatch('open-modal', id: 'edit-user-modal');
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$this->user->id,
            'phone_number' => 'nullable|string|max:20|unique:users,phone_number,'.$this->user->id,
            'password' => 'nullable|string|min:8',
            'loan_level_id' => 'nullable|exists:loan_levels,id',
        ]);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'loan_level_id' => $this->loan_level_id,
        ];

        if ($this->password) {
            $data['password'] = $this->password;
        }

        $this->user->update($data);
        $this->user->syncRoles($this->selectedRoles);

        $this->toastSuccess('User updated successfully.');
        $this->dispatch('close-modal', id: 'edit-user-modal');
        $this->dispatch('user-updated');
    }

    public function delete()
    {
        $this->user->delete();
        $this->toastSuccess('User deleted successfully.');
        $this->dispatch('close-modal', id: 'edit-user-modal');
        $this->dispatch('user-deleted');
    }

    public function render()
    {
        return $this->view([
            'roles' => Role::where('guard_name', 'web')->get(),
            'loanLevels' => LoanLevel::active()->get(),
        ])->layout('layouts::admin');
    }
}; ?>

<x-ui.modal 
    id="edit-user-modal"
    heading="Edit User" 
    description="Update user information and roles"
    width="lg"
>
    @if($user)
        <form wire:submit="save" class="space-y-6">
            <x-ui.field>
                <x-ui.label for="edit_user_name">Name</x-ui.label>
                <x-ui.input wire:model="name" id="edit_user_name" />
                <x-ui.error name="name" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label for="edit_user_email">Email</x-ui.label>
                <x-ui.input wire:model="email" id="edit_user_email" type="email" />
                <x-ui.error name="email" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label for="edit_user_phone">Phone Number</x-ui.label>
                <x-ui.input wire:model="phone_number" id="edit_user_phone" type="tel" />
                <x-ui.error name="phone_number" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label for="edit_user_password">Password (leave blank to keep current)</x-ui.label>
                <x-ui.input wire:model="password" id="edit_user_password" type="password" placeholder="••••••••" />
                <x-ui.error name="password" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label for="edit_user_loan_level">Loan Level</x-ui.label>
                <x-ui.select wire:model="loan_level_id" id="edit_user_loan_level">
                    <x-ui.select.option value="">No Loan Level</x-ui.select.option>
                    @foreach($loanLevels as $level)
                        <x-ui.select.option value="{{ $level->id }}">{{ $level->name }} (Max: {{ Number::currency($level->maximum_loan_amount) }})</x-ui.select.option>
                    @endforeach
                </x-ui.select>
                <x-ui.error name="loan_level_id" />
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
                                id="edit_user_role_{{ $role->id }}"
                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500"
                            >
                            <label for="edit_user_role_{{ $role->id }}" class="text-sm text-gray-700 dark:text-gray-300">
                                {{ $role->name }}
                            </label>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No roles available</p>
                    @endforelse
                </div>
            </div>

            <div class="flex justify-between items-center pt-4">
                <x-ui.button 
                    type="button" 
                    wire:click="delete" 
                    wire:confirm="Are you sure you want to delete this user?"
                    variant="danger" 
                    outline
                >
                    Delete
                </x-ui.button>
                <div class="flex gap-3">
                    <x-ui.button type="button" x-on:click="$dispatch('close-modal', {id: 'edit-user-modal'})" variant="outline">
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
