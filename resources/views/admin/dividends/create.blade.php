<?php

use App\Livewire\Concerns\HasToast;
use App\Models\Dividend;
use Livewire\Attributes\Rule;
use Livewire\Component;

new class extends Component
{
    use HasToast;

    #[Rule('required|string|max:255')]
    public string $type = 'cash'; // cash, stock, etc.

    #[Rule('required|numeric|min:0.0001')]
    public float $amount_per_share = 0.0;

    #[Rule('required|string|size:3')]
    public string $currency = 'NGN';

    #[Rule('required|date')]
    public string $declaration_date = '';

    #[Rule('required|date|after_or_equal:declaration_date')]
    public string $ex_dividend_date = '';

    #[Rule('required|date|after_or_equal:ex_dividend_date')]
    public string $record_date = '';

    #[Rule('required|date|after_or_equal:record_date')]
    public string $payment_date = '';

    public function mount()
    {
        $this->declaration_date = now()->format('Y-m-d');
    }

    public function save()
    {
        $this->validate();

        Dividend::create([
            'type' => $this->type,
            'amount_per_share' => $this->amount_per_share,
            'currency' => $this->currency,
            'declaration_date' => $this->declaration_date,
            'ex_dividend_date' => $this->ex_dividend_date,
            'record_date' => $this->record_date,
            'payment_date' => $this->payment_date,
            'paid_out' => false,
        ]);

        $this->toastSuccess('Dividend declared successfully.');

        return redirect()->route('admin.dividends.index');
    }

    public function render()
    {
        return $this->view()->layout('layouts::admin');
    }
}; ?>

<div class="max-w-2xl mx-auto p-6 space-y-6">
    <x-page-header 
        heading="Declare Dividend" 
        description="Create a new dividend declaration"
    >
        <x-slot:actions>
            <x-ui.button tag="a" href="{{ route('admin.dividends.index') }}" variant="outline">
                Cancel
            </x-ui.button>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <form wire:submit="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-ui.field>
                    <x-ui.label for="type">Type</x-ui.label>
                    <x-ui.select wire:model="type" id="type">
                        <x-ui.select.option value="cash">Cash</x-ui.select.option>
                    </x-ui.select>
                    <x-ui.error name="type" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="currency">Currency</x-ui.label>
                    <x-ui.select wire:model="currency" id="currency">
                        <x-ui.select.option value="NGN">NGN</x-ui.select.option>
                        <x-ui.select.option value="USD">USD</x-ui.select.option>
                    </x-ui.select>
                    <x-ui.error name="currency" />
                </x-ui.field>
            </div>

            <x-ui.field>
                <x-ui.label for="amount_per_share">Amount Per Share</x-ui.label>
                <x-ui.input wire:model="amount_per_share" id="amount_per_share" type="number" step="0.0001" />
                <x-ui.error name="amount_per_share" />
            </x-ui.field>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-ui.field>
                    <x-ui.label for="declaration_date">Declaration Date</x-ui.label>
                    <x-ui.input wire:model="declaration_date" id="declaration_date" type="date" />
                    <x-ui.error name="declaration_date" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="ex_dividend_date">Ex-Dividend Date</x-ui.label>
                    <x-ui.input wire:model="ex_dividend_date" id="ex_dividend_date" type="date" />
                    <x-ui.error name="ex_dividend_date" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="record_date">Record Date</x-ui.label>
                    <x-ui.input wire:model="record_date" id="record_date" type="date" />
                    <x-ui.error name="record_date" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="payment_date">Payment Date</x-ui.label>
                    <x-ui.input wire:model="payment_date" id="payment_date" type="date" />
                    <x-ui.error name="payment_date" />
                </x-ui.field>
            </div>

            <div class="flex justify-end pt-4">
                <x-ui.button type="submit" variant="primary">
                    Declare Dividend
                </x-ui.button>
            </div>
        </form>
    </div>
</div>
