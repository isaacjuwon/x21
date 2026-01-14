<?php

use App\Livewire\Concerns\HasToast;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination, HasToast;

    #[Rule('nullable|string')]
    public string $search = '';

    #[Rule('required|min:1')]
    public array $selectedUsers = [];

    #[Rule('required|string|min:3')]
    public string $subject = '';

    #[Rule('required|string|min:10')]
    public string $message = '';

    public bool $selectAll = false;

    #[Computed]
    public function users()
    {
        return User::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('email', 'like', '%'.$this->search.'%'))
            ->latest()
            ->paginate(10);
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedUsers = $this->users()->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedUsers = [];
        }
    }

    public function send()
    {
        $this->validate();

        // Check if any users are selected
        if (empty($this->selectedUsers)) {
             $this->addError('selectedUsers', 'Please select at least one user.');
             return;
        }

        $count = count($this->selectedUsers);
        
        // Logic to dispatch mail job would go here
        // For now, we simulate it
        
        // Log::info("Sending email to {$count} users: {$this->subject}");

        $this->reset(['selectedUsers', 'subject', 'message', 'selectAll']);
        $this->toastSuccess("Email sent to {$count} users successfully.");
    }

    public function render()
    {
        return $this->view()
            ->title('Send Mail')
            ->layout('layouts::admin');
    }
}; ?>

<div class="max-w-7xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Compose Mail</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Send emails to users</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Selection -->
        <div class="lg:col-span-2 space-y-4">
             <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden flex flex-col h-[600px]">
                <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex flex-col gap-3">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Select Recipients</h2>
                    <x-ui.input 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Search users..." 
                        type="search"
                    >
                        <x-slot:leading>
                            <x-ui.icon name="magnifying-glass" class="w-5 h-5 text-gray-400" />
                        </x-slot:leading>
                    </x-ui.input>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-medium sticky top-0 backdrop-blur-sm z-10">
                            <tr>
                                <th class="px-6 py-4 w-10">
                                    <x-ui.checkbox wire:model.live="selectAll" />
                                </th>
                                <th class="px-6 py-4">Name</th>
                                <th class="px-6 py-4">Email</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($this->users as $user)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors" wire:key="user-{{ $user->id }}">
                                    <td class="px-6 py-4">
                                        <x-ui.checkbox wire:model.live="selectedUsers" value="{{ $user->id }}" />
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                        {{ $user->name }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                        {{ $user->email }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        No users found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                 <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $this->users->links() }}
                </div>
            </div>
        </div>

        <!-- Compose Form -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 sticky top-6">
                <form wire:submit="send" class="space-y-6">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                             <x-ui.label for="subject">Subject</x-ui.label>
                             <span class="text-xs text-gray-500">
                                {{ count($selectedUsers) }} recipients selected
                             </span>
                        </div>
                       
                        <x-ui.input wire:model="subject" id="subject" placeholder="Email subject" />
                        <x-ui.error name="subject" />
                    </div>

                    <x-ui.field>
                        <x-ui.label for="message">Message</x-ui.label>
                        <x-ui.textarea wire:model="message" id="message" rows="12" placeholder="Write your message here..." />
                        <x-ui.error name="message" />
                    </x-ui.field>
                    
                    <x-ui.error name="selectedUsers" />

                    <div class="pt-2">
                        <x-ui.button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                            <span wire:loading.remove>Send Email</span>
                            <span wire:loading>Sending...</span>
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
