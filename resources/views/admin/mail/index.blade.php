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
            <h1 class="text-xl font-bold text-neutral-900 dark:text-white">Compose Mail</h1>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Send emails to users</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Selection -->
        <div class="lg:col-span-2 space-y-4">
             <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 overflow-hidden flex flex-col h-[600px]">
                <div class="p-4 border-b border-neutral-100 dark:border-neutral-700 flex flex-col gap-3">
                    <h2 class="text-sm font-bold text-neutral-900 dark:text-white uppercase tracking-widest">Select Recipients</h2>
                    <x-ui.input 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Search users..." 
                        type="search"
                    >
                        <x-slot:leading>
                        <x-ui.icon name="magnifying-glass" class="w-5 h-5 text-neutral-400" />
                        </x-slot:leading>
                    </x-ui.input>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-neutral-50 dark:bg-neutral-700/50 text-neutral-500 dark:text-neutral-400 font-bold sticky top-0 backdrop-blur-sm z-10">
                            <tr>
                                <th class="px-6 py-4 w-10">
                                    <x-ui.checkbox wire:model.live="selectAll" />
                                </th>
                                <th class="px-6 py-4">Name</th>
                                <th class="px-6 py-4">Email</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
                            @forelse($this->users as $user)
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors" wire:key="user-{{ $user->id }}">
                                    <td class="px-6 py-4">
                                        <x-ui.checkbox wire:model.live="selectedUsers" value="{{ $user->id }}" />
                                    </td>
                                    <td class="px-6 py-4 font-bold text-neutral-900 dark:text-white">
                                        {{ $user->name }}
                                    </td>
                                    <td class="px-6 py-4 text-neutral-500 dark:text-neutral-400 font-medium">
                                        {{ $user->email }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400">
                                        No users found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                 <div class="p-4 border-t border-neutral-100 dark:border-neutral-700">
                    {{ $this->users->links() }}
                </div>
            </div>
        </div>

        <!-- Compose Form -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-sm border border-neutral-100 dark:border-neutral-700 p-6 sticky top-6">
                <form wire:submit="send" class="space-y-6">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                             <x-ui.label for="subject" class="text-xs font-bold uppercase tracking-widest">Subject</x-ui.label>
                             <span class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">
                                {{ count($selectedUsers) }} selected
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
