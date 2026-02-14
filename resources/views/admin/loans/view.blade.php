<?php

use App\Actions\Loans\DisburseLoanAction;
use App\Enums\LoanStatus;
use App\Events\Loans\LoanApproved;
use App\Events\Loans\LoanRejected;
use App\Livewire\Concerns\HasToast;
use App\Models\Loan;
use Livewire\Attributes\Rule;
use Livewire\Component;

new class extends Component
{
    use HasToast;

    public Loan $loan;

    #[Rule('required|in:pending,approved,active,fully_paid,rejected,defaulted')]
    public string $status = '';

    public function mount(Loan $loan)
    {
        $this->loan = $loan->load(['user', 'payments' => fn ($q) => $q->latest()]);
        $this->status = $loan->status->value;
    }

    public function approve()
    {
        $this->loan->update([
            'status' => LoanStatus::APPROVED,
            'approved_at' => now(),
        ]);

        event(new LoanApproved($this->loan, $this->loan->user));

        $this->status = LoanStatus::APPROVED->value;
        $this->toastSuccess('Loan approved successfully.');
    }

    public function reject()
    {
        $this->loan->update([
            'status' => LoanStatus::REJECTED,
        ]);

        event(new LoanRejected($this->loan, $this->loan->user));

        $this->status = LoanStatus::REJECTED->value;
        $this->toastSuccess('Loan rejected.');
    }

    public function disburse(DisburseLoanAction $disburseAction)
    {
        try {
            $disburseAction->execute($this->loan);

            $this->loan = $this->loan->fresh();
            $this->status = LoanStatus::ACTIVE->value;
            $this->toastSuccess('Loan disbursed and active.');
        } catch (\Exception $e) {
            $this->toastError($e->getMessage());
        }
    }

    public function markAsDefaulted()
    {
        $this->loan->update([
            'status' => LoanStatus::DEFAULTED,
        ]);

        $this->status = LoanStatus::DEFAULTED->value;
        $this->toastSuccess('Loan marked as defaulted.');
    }

    public function delete()
    {
        $this->loan->delete();
        $this->toastSuccess('Loan deleted successfully.');

        return redirect()->route('admin.loans.index');
    }

    public function save()
    {
        $this->validate();

        $newStatus = LoanStatus::from($this->status);

        match ($newStatus) {
            LoanStatus::APPROVED => $this->approve(),
            LoanStatus::REJECTED => $this->reject(),
            LoanStatus::ACTIVE => $this->disburse(),
            default => $this->updateStatus($newStatus),
        };
    }

    protected function updateStatus($newStatus)
    {
        $this->loan->update(['status' => $newStatus]);
        $this->toastSuccess('Loan status updated successfully.');
    }

    public function render()
    {
        return $this->view()->layout('layouts::admin');
    }
}; ?>

<div class="p-6 space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <x-ui.heading variant="h1" class="text-xl font-bold text-neutral-900 dark:text-white">Loan Profile</x-ui.heading>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">#{{ $loan->id }} &bull; {{ $loan->user->name }}</p>
        </div>
        <div class="flex items-center gap-2">
            <x-ui.button tag="a" href="{{ route('admin.loans.index') }}" variant="ghost" icon="arrow-left">Back</x-ui.button>
            <div class="h-6 w-px bg-neutral-200 dark:bg-neutral-800 mx-1"></div>
            @if($loan->status === LoanStatus::PENDING)
                <x-ui.button wire:click="approve" variant="primary" icon="check" wire:confirm="Approve this loan?">Approve</x-ui.button>
                <x-ui.button wire:click="reject" variant="outline" color="red" icon="x-mark" wire:confirm="Reject this loan?">Reject</x-ui.button>
            @endif

            @if($loan->status === LoanStatus::APPROVED)
                <x-ui.button wire:click="disburse" variant="primary" icon="banknotes">Disburse Funds</x-ui.button>
            @endif

            @if($loan->status === LoanStatus::ACTIVE)
                <x-ui.button wire:click="markAsDefaulted" variant="outline" color="red" icon="shield-exclamation" wire:confirm="Mark this loan as defaulted?">Mark Defaulted</x-ui.button>
            @endif

            <x-ui.dropdown>
                <x-slot:button>
                    <x-ui.button variant="ghost" icon="ellipsis-vertical" squared />
                </x-slot:button>
                
                <x-slot:menu>
                    <x-ui.dropdown.item wire:click="delete" color="red" icon="trash" wire:confirm="Delete this loan?">
                        Delete Loan
                    </x-ui.dropdown.item>
                </x-slot:menu>
            </x-ui.dropdown>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-ui.card class="p-4 flex flex-col justify-between overflow-hidden relative">
            <div class="relative z-10 overflow-hidden">
                <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Total Repayment</p>
                <h3 class="text-xl font-extrabold mt-1 text-neutral-900 dark:text-white truncate" title="{{ Number::currency($loan->total_repayment) }}">{{ Number::currency($loan->total_repayment) }}</h3>
                <p class="text-[10px] text-neutral-400 mt-1 truncate">{{ Number::currency($loan->amount) }} + Interest</p>
            </div>
            <div class="absolute -right-4 -bottom-4 opacity-5 dark:opacity-10">
                <x-ui.icon name="banknotes" class="size-24" />
            </div>
        </x-ui.card>

        <x-ui.card class="p-4 flex flex-col justify-between overflow-hidden relative">
            <div class="relative z-10 overflow-hidden">
                <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Amount Paid</p>
                <h3 class="text-xl font-extrabold mt-1 text-success truncate" title="{{ Number::currency($loan->amount_paid) }}">{{ Number::currency($loan->amount_paid) }}</h3>
                <div class="flex items-center gap-2 mt-2">
                    <div class="flex-1 h-1.5 bg-neutral-100 dark:bg-neutral-800 rounded-full overflow-hidden">
                        <div class="h-full bg-success rounded-full transition-all duration-500" style="width: {{ $loan->progress_percentage }}%"></div>
                    </div>
                    <span class="text-[10px] font-bold">{{ $loan->progress_percentage }}%</span>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="p-4 flex flex-col justify-between overflow-hidden relative border-red-500/20">
            <div class="relative z-10 overflow-hidden">
                <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Remaining Balance</p>
                <h3 class="text-xl font-extrabold mt-1 text-error truncate" title="{{ Number::currency($loan->balance_remaining) }}">{{ Number::currency($loan->balance_remaining) }}</h3>
                <p class="text-[10px] text-neutral-400 mt-1">{{ $loan->installment_months }} Months Plan</p>
            </div>
        </x-ui.card>

        <x-ui.card class="p-4 flex flex-col justify-between overflow-hidden relative bg-neutral-900 border-neutral-800 dark:bg-white dark:border-white">
            <div class="relative z-10 overflow-hidden">
                <p class="text-[10px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-widest">Next Payment</p>
                <h3 class="text-xl font-extrabold mt-1 text-white dark:text-neutral-900 truncate">
                    {{ $loan->next_payment_date ? $loan->next_payment_date->format('M d, Y') : 'N/A' }}
                </h3>
                <p class="text-[10px] text-neutral-500 dark:text-neutral-400 mt-1 truncate">Amount: {{ Number::currency($loan->monthly_payment ?? 0) }}</p>
            </div>
        </x-ui.card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Repayment Schedule -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-neutral-900 dark:text-white">Repayment History</h3>
            </div>
            <x-ui.table>
                <x-slot:header>
                    <x-ui.table.header class="text-[10px] font-bold uppercase tracking-widest">Date</x-ui.table.header>
                    <x-ui.table.header class="text-[10px] font-bold uppercase tracking-widest">Reference</x-ui.table.header>
                    <x-ui.table.header class="text-[10px] font-bold uppercase tracking-widest">Amount</x-ui.table.header>
                    <x-ui.table.header class="text-[10px] font-bold uppercase tracking-widest">Balance After</x-ui.table.header>
                    <x-ui.table.header class="text-[10px] font-bold uppercase tracking-widest">Status</x-ui.table.header>
                </x-slot:header>
                <x-slot:body>
                    @forelse($loan->payments as $payment)
                        <x-ui.table.row class="text-xs">
                            <x-ui.table.cell class="font-bold text-neutral-900 dark:text-white">{{ $payment->created_at->format('M d, Y') }}</x-ui.table.cell>
                            <x-ui.table.cell class="text-neutral-500 uppercase text-[10px] tracking-widest">{{ $payment->reference ?? 'N/A' }}</x-ui.table.cell>
                            <x-ui.table.cell class="text-success font-bold">{{ Number::currency($payment->amount) }}</x-ui.table.cell>
                            <x-ui.table.cell class="text-neutral-500">{{ Number::currency($payment->balance_after) }}</x-ui.table.cell>
                            <x-ui.table.cell>
                                <x-ui.badge color="success" class="text-[10px]">Paid</x-ui.badge>
                            </x-ui.table.cell>
                        </x-ui.table.row>
                    @empty
                        <x-ui.table.row>
                            <x-ui.table.cell colspan="5" class="py-12 text-center text-neutral-400 italic">
                                No repayment history found.
                            </x-ui.table.cell>
                        </x-ui.table.row>
                    @endforelse
                </x-slot:body>
            </x-ui.table>
        </div>

        <!-- Details & Status Update -->
        <div class="space-y-6">
            <x-ui.card class="p-6">
                <h3 class="text-base font-bold mb-4 text-neutral-900 dark:text-white">Loan Details</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between text-xs py-2 border-b border-neutral-100 dark:border-neutral-700/50">
                        <span class="text-neutral-500 uppercase tracking-widest text-[10px] font-bold">Interest Rate</span>
                        <span class="font-bold text-neutral-900 dark:text-white">{{ $loan->interest_rate }}%</span>
                    </div>
                    <div class="flex items-center justify-between text-xs py-2 border-b border-neutral-100 dark:border-neutral-700/50">
                        <span class="text-neutral-500 uppercase tracking-widest text-[10px] font-bold">Duration</span>
                        <span class="font-bold text-neutral-900 dark:text-white">{{ $loan->installment_months }} Months</span>
                    </div>
                    <div class="flex items-center justify-between text-xs py-2 border-b border-neutral-100 dark:border-neutral-700/50">
                        <span class="text-neutral-500 uppercase tracking-widest text-[10px] font-bold">Monthly Installment</span>
                        <span class="font-bold text-neutral-900 dark:text-white">{{ Number::currency($loan->monthly_payment ?? 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-xs py-2 border-b border-neutral-100 dark:border-neutral-700/50">
                        <span class="text-neutral-500 uppercase tracking-widest text-[10px] font-bold">Shares Required</span>
                        <span class="font-bold text-neutral-900 dark:text-white">{{ $loan->shares_required }} Units</span>
                    </div>
                    <div class="flex items-center justify-between text-xs py-2">
                        <span class="text-neutral-500 uppercase tracking-widest text-[10px] font-bold">Current Status</span>
                        <x-ui.badge :color="$loan->status_badge" class="text-[10px]">{{ $loan->status->getLabel() }}</x-ui.badge>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="p-6">
                <h3 class="text-base font-bold mb-4 text-neutral-900 dark:text-white">Update Status</h3>
                <form wire:submit="save" class="space-y-4">
                    <x-ui.field>
                        <x-ui.label for="status">Manual Override</x-ui.label>
                        <x-ui.select wire:model="status" id="status">
                            @foreach(LoanStatus::cases() as $status)
                                <x-ui.select.option value="{{ $status->value }}">{{ $status->getLabel() }}</x-ui.select.option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.error name="status" />
                    </x-ui.field>
                    <x-ui.button type="submit" variant="outline" class="w-full">Save Status Change</x-ui.button>
                </form>
            </x-ui.card>
        </div>
    </div>
</div>
