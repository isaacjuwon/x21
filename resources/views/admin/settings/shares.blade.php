<?php

use App\Livewire\Concerns\HasToast;
use App\Settings\ShareSettings;
use Livewire\Component;

new class extends Component
{
    use HasToast;

    public bool $require_admin_approval;

    public float $share_price;

    public float $share_interest_rate;

    public int $holding_period;

    public function mount(ShareSettings $settings): void
    {
        $this->require_admin_approval = $settings->require_admin_approval;
        $this->share_price = $settings->share_price;
        $this->share_interest_rate = $settings->share_interest_rate;
        $this->holding_period = $settings->holding_period;
    }

    public function save(ShareSettings $settings)
    {
        $settings->require_admin_approval = $this->require_admin_approval;
        $settings->share_price = $this->share_price;
        $settings->share_interest_rate = $this->share_interest_rate;
        $settings->holding_period = $this->holding_period;
        $settings->save();

        $this->toastSuccess('Share settings updated successfully.');
    }

    public function render()
    {
        return $this->view()
            ->title('Share Settings')
            ->layout('layouts::admin');
    }
};
?>

<section class="w-full">


    <x-layouts.admin.settings heading="Share Settings" subheading="Configure share purchase and management settings">
        <form wire:submit="save" class="my-6 w-full space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-ui.field>
                    <x-ui.label for="share_price">Share Price (NGN)</x-ui.label>
                    <x-ui.input wire:model="share_price" id="share_price" type="number" step="0.01" min="0.01" />
                    <x-ui.error name="share_price" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="holding_period">Holding Period (Days)</x-ui.label>
                    <x-ui.input wire:model="holding_period" id="holding_period" type="number" min="0" />
                    <x-ui.error name="holding_period" />
                </x-ui.field>

                <x-ui.field>
                    <x-ui.label for="share_interest_rate">Interest Rate (%)</x-ui.label>
                    <x-ui.input wire:model="share_interest_rate" id="share_interest_rate" type="number" step="0.01" min="0" />
                    <x-ui.error name="share_interest_rate" />
                </x-ui.field>
            </div>

            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <x-ui.checkbox wire:model="require_admin_approval" id="require_admin_approval" />
                    <x-ui.label for="require_admin_approval" class="mb-0">Require Admin Approval</x-ui.label>
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
