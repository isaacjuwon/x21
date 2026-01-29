<?php

declare(strict_types=1);

use App\Enums\WalletType;
use App\Livewire\Concerns\HasToast;
use App\Models\User;
use App\Models\WalletTransaction;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Rule;
use Livewire\Component;

new class extends Component
{
    use HasToast;

    public User $user;

    #[Rule('required|numeric|min:0.01')]
    public float $amount = 0;

    #[Rule('required|string|in:credit,debit')]
    public string $action = 'credit';

    #[Rule('required|string')]
    public string $walletType = 'main';

    #[Rule('required|string|min:3')]
    public string $notes = '';

    public function mount(User $user)
    {
        $this->user = $user;
        // Ensure wallets exist
        foreach (WalletType::cases() as $type) {
            $user->getOrCreateWallet($type);
        }
    }

    public function processOperation()
    {
        $this->validate();

        $type = WalletType::from($this->walletType);
        $wallet = $this->user->getOrCreateWallet($type);

        try {
            if ($this->action === 'credit') {
                $wallet->incrementAndCreateLog($this->amount, $this->notes.' (Admin Manual Credit)');
                $this->toastSuccess('Wallet credited successfully.');
            } else {
                if (! $wallet->hasSufficientBalance($this->amount)) {
                    $this->addError('amount', 'Insufficient balance in '.$type->getLabel());

                    return;
                }
                $wallet->decrementAndCreateLog($this->amount, $this->notes.' (Admin Manual Debit)');
                $this->toastSuccess('Wallet debited successfully.');
            }

            $this->reset(['amount', 'notes']);
        } catch (\Exception $e) {
            $this->toastError('Operation failed: '.$e->getMessage());
        }
    }

    #[Computed]
    public function transactions()
    {
        return WalletTransaction::where('loggable_type', User::class)
            ->where('loggable_id', $this->user->id)
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return $this->view()->layout('layouts::admin');
    }
}; ?>

<div class="p-6 space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <x-ui.heading>Wallet Management</x-ui.heading>
            <p class="text-neutral-500 dark:text-neutral-400">{{ $user->name }} &bull; {{ $user->email }}</p>
        </div>
        <div class="flex items-center gap-2">
            <x-ui.button tag="a" href="{{ route('admin.wallets.index') }}" variant="ghost" icon="arrow-left">Back</x-ui.button>
            <div class="h-6 w-px bg-neutral-200 dark:bg-neutral-800 mx-1"></div>
            
            <x-ui.dropdown>
                <x-slot:button>
                    <x-ui.button variant="ghost" icon="ellipsis-vertical" squared />
                </x-slot:button>
                <x-slot:menu>
                    <x-ui.dropdown.item :href="route('admin.users.edit', $user)" icon="user">Edit User Profile</x-ui.dropdown.item>
                    <x-ui.dropdown.item :href="route('admin.transactions.index', ['user_id' => $user->id])" icon="list-bullet">Full History</x-ui.dropdown.item>
                </x-slot:menu>
            </x-ui.dropdown>
        </div>
    </div>

    <!-- Balance Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach(WalletType::cases() as $type)
            @php
                $balance = $user->getWalletBalanceByType($type);
                $icon = match($type) {
                    WalletType::MAIN => 'wallet',
                    WalletType::BONUS => 'gift',
                    default => 'currency-dollar',
                };
                $color = match($type) {
                    WalletType::MAIN => 'primary',
                    WalletType::BONUS => 'accent',
                    default => 'neutral',
                };
            @endphp
            <x-ui.card class="p-4 flex flex-col justify-between overflow-hidden relative rounded-[--radius-box]" wire:key="balance-{{ $type->value }}">
                <div class="relative z-10">
                    <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">{{ $type->getLabel() }}</p>
                    <h3 class="text-2xl font-bold mt-1 text-neutral-900 dark:text-white">
                        {{ \Illuminate\Support\Number::currency($balance) }}
                    </h3>
                </div>
                <div class="absolute -right-6 -bottom-6 opacity-5 dark:opacity-10 text-[--color-{{ $color }}]">
                    <x-ui.icon :name="$icon" class="size-24" />
                </div>
            </x-ui.card>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Adjustment Form -->
        <div class="lg:col-span-1">
            <x-ui.card class="p-6 h-full rounded-[--radius-box]">
                <h3 class="text-lg font-bold mb-6 text-neutral-900 dark:text-white">Manual Adjustment</h3>
                <form wire:submit="processOperation" class="space-y-5">
                    <x-ui.field>
                        <x-ui.label>Target Wallet</x-ui.label>
                        <x-ui.select wire:model="walletType">
                            @foreach(WalletType::cases() as $case)
                                <x-ui.select.option value="{{ $case->value }}">{{ $case->getLabel() }}</x-ui.select.option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.error name="walletType" />
                    </x-ui.field>

                    <x-ui.field>
                        <x-ui.label>Adjustment Type</x-ui.label>
                        <div class="flex p-1 bg-neutral-100 dark:bg-neutral-900 rounded-[--radius-box]">
                            <button type="button" wire:click="$set('action', 'credit')" 
                                class="flex-1 flex items-center justify-center gap-2 py-2 px-3 rounded-[--radius-field] transition-all {{ $action === 'credit' ? 'bg-white dark:bg-neutral-800 text-success shadow-sm' : 'text-neutral-500' }}">
                                <x-ui.icon name="plus-circle" class="size-4" />
                                <span class="text-sm font-semibold">Credit</span>
                            </button>
                            <button type="button" wire:click="$set('action', 'debit')" 
                                class="flex-1 flex items-center justify-center gap-2 py-2 px-3 rounded-[--radius-field] transition-all {{ $action === 'debit' ? 'bg-white dark:bg-neutral-800 text-error shadow-sm' : 'text-neutral-500' }}">
                                <x-ui.icon name="minus-circle" class="size-4" />
                                <span class="text-sm font-semibold">Debit</span>
                            </button>
                        </div>
                    </x-ui.field>

                    <x-ui.field>
                        <x-ui.label>Transaction Amount</x-ui.label>
                        <x-ui.input wire:model="amount" type="number" step="0.01" placeholder="0.00">
                            <x-slot:prepend>
                                <span class="text-neutral-500 font-bold">₦</span>
                            </x-slot:prepend>
                        </x-ui.input>
                        <x-ui.error name="amount" />
                    </x-ui.field>

                    <x-ui.field>
                        <x-ui.label>Administrative Notes</x-ui.label>
                        <x-ui.textarea wire:model="notes" placeholder="Detailed reason for this manual adjustment..." rows="3" />
                        <x-ui.error name="notes" />
                    </x-ui.field>

                    <x-ui.button type="submit" variant="primary" class="w-full">
                        Process {{ ucfirst($action) }}
                    </x-ui.button>
                </form>
            </x-ui.card>
        </div>

        <!-- Transaction History Table -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white">Recent Transactions</h3>
            </div>
            
            <div class="relative overflow-x-auto border border-black/10 dark:border-white/10 rounded-[--radius-box] bg-white dark:bg-neutral-900 shadow-sm">
                <table class="w-full text-left border-collapse text-sm">
                    <thead class="bg-neutral-50 dark:bg-neutral-800/50 border-b border-black/10 dark:border-white/10">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Reference</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Wallet</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Movement</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($this->transactions as $transaction)
                            @continue(!$transaction)
                            <tr class="group hover:bg-neutral-50 dark:hover:bg-white/5 transition-colors" wire:key="tx-{{ $transaction->id }}">
                                <td class="px-4 py-4">
                                    <p class="font-mono text-xs font-bold text-neutral-900 dark:text-white uppercase">{{ $transaction->reference }}</p>
                                    <p class="text-[10px] text-neutral-400 mt-0.5">{{ $transaction->created_at?->format('M d, Y H:i') }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <x-ui.badge color="neutral" class="text-[10px]">
                                        {{ $transaction->wallet_type?->getLabel() ?? 'Default' }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold {{ $transaction->transaction_type === 'increment' ? 'text-success' : 'text-error' }}">
                                            {{ $transaction->transaction_type === 'increment' ? '+' : '-' }}
                                            {{ \Illuminate\Support\Number::currency($transaction->amount ?? 0) }}
                                        </span>
                                        <span class="text-[10px] text-neutral-400">
                                            {{ \Illuminate\Support\Number::currency($transaction->from_balance ?? 0) }} → {{ \Illuminate\Support\Number::currency($transaction->to_balance ?? 0) }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-12 text-center text-neutral-400 italic">
                                    No transaction history found for this user.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($this->transactions->hasPages())
                <div class="pt-2">
                    {{ $this->transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
