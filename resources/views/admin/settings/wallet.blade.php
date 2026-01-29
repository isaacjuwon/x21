<?php

use App\Livewire\Concerns\HasToast;
use App\Settings\WalletSettings;
use Livewire\Component;

new class extends Component
{
    use HasToast;

    public bool $wallet_enabled;

    public int $min_funding_amount;

    public int $max_funding_amount;

    public int $min_withdrawal_amount;

    public int $max_withdrawal_amount;

    public float $withdrawal_fee_percentage;

    public int $withdrawal_fee_cap;

    public bool $instant_withdrawal_enabled;

    public int $withdrawal_processing_hours;

    public int $daily_withdrawal_limit;

    public int $monthly_withdrawal_limit;

    public function mount(WalletSettings $settings): void
    {
        $this->wallet_enabled = $settings->wallet_enabled;
        $this->min_funding_amount = $settings->min_funding_amount;
        $this->max_funding_amount = $settings->max_funding_amount;
        $this->min_withdrawal_amount = $settings->min_withdrawal_amount;
        $this->max_withdrawal_amount = $settings->max_withdrawal_amount;
        $this->withdrawal_fee_percentage = $settings->withdrawal_fee_percentage;
        $this->withdrawal_fee_cap = $settings->withdrawal_fee_cap;
        $this->instant_withdrawal_enabled = $settings->instant_withdrawal_enabled;
        $this->withdrawal_processing_hours = $settings->withdrawal_processing_hours;
        $this->daily_withdrawal_limit = $settings->daily_withdrawal_limit;
        $this->monthly_withdrawal_limit = $settings->monthly_withdrawal_limit;
    }

    public function save(WalletSettings $settings)
    {
        $settings->wallet_enabled = $this->wallet_enabled;
        $settings->min_funding_amount = $this->min_funding_amount;
        $settings->max_funding_amount = $this->max_funding_amount;
        $settings->min_withdrawal_amount = $this->min_withdrawal_amount;
        $settings->max_withdrawal_amount = $this->max_withdrawal_amount;
        $settings->withdrawal_fee_percentage = $this->withdrawal_fee_percentage;
        $settings->withdrawal_fee_cap = $this->withdrawal_fee_cap;
        $settings->instant_withdrawal_enabled = $this->instant_withdrawal_enabled;
        $settings->withdrawal_processing_hours = $this->withdrawal_processing_hours;
        $settings->daily_withdrawal_limit = $this->daily_withdrawal_limit;
        $settings->monthly_withdrawal_limit = $this->monthly_withdrawal_limit;
        $settings->save();

        $this->toastSuccess('Wallet settings updated successfully.');
    }

    public function render()
    {
        return $this->view()
            ->title('Wallet Settings')
            ->layout('layouts::admin');
    }
};
?>

<section class="w-full">


    <x-layouts.admin.settings heading="Wallet Settings" subheading="Configure wallet funding and withdrawal settings">
        <form wire:submit="save" class="my-6 w-full space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-ui.field>
                    <x-ui.label for="min_funding_amount">Minimum Funding Amount</x-ui.label>
                    <x-ui.input wire:model="min_funding_amount" id="min_funding_amount" type="number" />
                    <x-ui.error name="min_funding_amount" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="max_funding_amount">Maximum Funding Amount</x-ui.label>
                    <x-ui.input wire:model="max_funding_amount" id="max_funding_amount" type="number" />
                    <x-ui.error name="max_funding_amount" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="min_withdrawal_amount">Minimum Withdrawal Amount</x-ui.label>
                    <x-ui.input wire:model="min_withdrawal_amount" id="min_withdrawal_amount" type="number" />
                    <x-ui.error name="min_withdrawal_amount" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="max_withdrawal_amount">Maximum Withdrawal Amount</x-ui.label>
                    <x-ui.input wire:model="max_withdrawal_amount" id="max_withdrawal_amount" type="number" />
                    <x-ui.error name="max_withdrawal_amount" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="withdrawal_fee_percentage">Withdrawal Fee (%)</x-ui.label>
                    <x-ui.input wire:model="withdrawal_fee_percentage" id="withdrawal_fee_percentage" type="number" step="0.01" />
                    <x-ui.error name="withdrawal_fee_percentage" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="withdrawal_fee_cap">Withdrawal Fee Cap</x-ui.label>
                    <x-ui.input wire:model="withdrawal_fee_cap" id="withdrawal_fee_cap" type="number" />
                    <x-ui.error name="withdrawal_fee_cap" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="withdrawal_processing_hours">Processing Hours</x-ui.label>
                    <x-ui.input wire:model="withdrawal_processing_hours" id="withdrawal_processing_hours" type="number" />
                    <x-ui.error name="withdrawal_processing_hours" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="daily_withdrawal_limit">Daily Withdrawal Limit</x-ui.label>
                    <x-ui.input wire:model="daily_withdrawal_limit" id="daily_withdrawal_limit" type="number" />
                    <x-ui.error name="daily_withdrawal_limit" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="monthly_withdrawal_limit">Monthly Withdrawal Limit</x-ui.label>
                    <x-ui.input wire:model="monthly_withdrawal_limit" id="monthly_withdrawal_limit" type="number" />
                    <x-ui.error name="monthly_withdrawal_limit" />
                </x-ui.field>
            </div>

            <div class="space-y-3 pt-6 border-t border-neutral-100 dark:border-neutral-700">
                <div class="flex items-center gap-2">
                    <x-ui.checkbox wire:model="wallet_enabled" id="wallet_enabled" />
                    <x-ui.label for="wallet_enabled" class="mb-0">Enable Wallet System</x-ui.label>
                </div>
                
                <div class="flex items-center gap-2">
                    <x-ui.checkbox wire:model="instant_withdrawal_enabled" id="instant_withdrawal_enabled" />
                    <x-ui.label for="instant_withdrawal_enabled" class="mb-0">Enable Instant Withdrawal</x-ui.label>
                </div>
            </div>

            <div class="flex items-center justify-end">
                <x-ui.button variant="primary" type="submit" class="w-full md:w-auto">
                    Save
                </x-ui.button>
            </div>
        </form>
    </x-layouts.admin.settings>
</section>
