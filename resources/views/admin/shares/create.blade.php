<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use App\Models\Share;
use App\Models\User;
use App\Livewire\Concerns\HasToast;

new class extends Component {
    use HasToast;

    #[Rule('required|exists:users,id')]
    public ?int $user_id = null;

    #[Rule('required|integer|min:1')]
    public int $quantity = 1;

    #[Rule('required|string')]
    public string $currency = 'Units';

    public function save()
    {
        $this->validate();

        $user = User::find($this->user_id);

        Share::create([
            'holder_type' => User::class,
            'holder_id' => $user->id,
            'quantity' => $this->quantity,
            'currency' => $this->currency,
            'status' => \App\Enums\ShareStatus::APPROVED, // Admin created shares are approved by default
            'approved_at' => now(),
        ]);

        $this->toastSuccess('Share created successfully.');

        return redirect()->route('admin.shares.index');
    }

    public function render()
    {
        return $this->view()->with([
            'users' => User::orderBy('name')->get(),
        ])->layout('layouts::admin');
    }
}; ?>

<div class="max-w-2xl mx-auto p-6 space-y-6">
    <x-page-header 
        heading="Create Share" 
        description="Allocate shares to a user"
    >
        <x-slot:actions>
            <x-ui.button tag="a" href="{{ route('admin.shares.index') }}" variant="outline">
                Cancel
            </x-ui.button>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <form wire:submit="save" class="space-y-6">
            <x-ui.field>
                <x-ui.label for="user_id">Holder (User)</x-ui.label>
                <x-ui.select wire:model="user_id" id="user_id">
                    <x-ui.select.option value="">Select a user</x-ui.select.option>
                    @foreach($users as $user)
                        <x-ui.select.option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</x-ui.select.option>
                    @endforeach
                </x-ui.select>
                <x-ui.error name="user_id" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label for="quantity">Quantity</x-ui.label>
                <x-ui.input wire:model="quantity" id="quantity" type="number" min="1" />
                <x-ui.error name="quantity" />
            </x-ui.field>

            <x-ui.field>
                <x-ui.label for="currency">Currency</x-ui.label>
                <x-ui.input wire:model="currency" id="currency" readonly />
                <x-ui.error name="currency" />
            </x-ui.field>

            <div class="flex justify-end pt-4">
                <x-ui.button type="submit" variant="primary">
                    Create Share
                </x-ui.button>
            </div>
        </form>
    </div>
</div>
