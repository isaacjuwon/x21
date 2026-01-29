<?php

use App\Livewire\Concerns\HasToast;
use App\Settings\IntegrationSettings;
use Livewire\Component;

new class extends Component
{
    use HasToast;

    // Paystack
    public $paystack_public_key;
    public $paystack_secret_key;
    public $paystack_url;

    // Epins
    public $epins_api_key;
    public $epins_url;
    public $epins_sandbox_url;

    // Dojah
    public $dojah_api_key;
    public $dojah_app_id;
    public $dojah_base_url;

    public function mount(IntegrationSettings $settings): void
    {
        $this->paystack_public_key = $settings->paystack_public_key;
        $this->paystack_secret_key = $settings->paystack_secret_key;
        $this->paystack_url = $settings->paystack_url;

        $this->epins_api_key = $settings->epins_api_key;
        $this->epins_url = $settings->epins_url;
        $this->epins_sandbox_url = $settings->epins_sandbox_url;

        $this->dojah_api_key = $settings->dojah_api_key;
        $this->dojah_app_id = $settings->dojah_app_id;
        $this->dojah_base_url = $settings->dojah_base_url;
    }

    public function save(IntegrationSettings $settings)
    {
        $settings->paystack_public_key = $this->paystack_public_key;
        $settings->paystack_secret_key = $this->paystack_secret_key;
        $settings->paystack_url = $this->paystack_url;

        $settings->epins_api_key = $this->epins_api_key;
        $settings->epins_url = $this->epins_url;
        $settings->epins_sandbox_url = $this->epins_sandbox_url;

        $settings->dojah_api_key = $this->dojah_api_key;
        $settings->dojah_app_id = $this->dojah_app_id;
        $settings->dojah_base_url = $this->dojah_base_url;

        $settings->save();

        $this->toastSuccess('Integration settings updated successfully.');
    }

    public function render()
    {
        return $this->view()
            ->title('Integration Settings')
            ->layout('layouts::admin');
    }
};
?>

<section class="w-full">
    <x-layouts.admin.settings heading="Integration Settings" subheading="Manage third-party service credentials">
        <form wire:submit="save" class="mx-6 space-y-8">
            
            <div class="space-y-6">
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white border-b border-neutral-100 dark:border-neutral-700 pb-2">Paystack</h3>
                <div class="grid grid-cols-1 gap-6">
                    <x-ui.field>
                        <x-ui.label for="paystack_public_key">Public Key</x-ui.label>
                        <x-ui.input wire:model="paystack_public_key" id="paystack_public_key" />
                        <x-ui.error name="paystack_public_key" />
                    </x-ui.field>

                    <x-ui.field>
                        <x-ui.label for="paystack_secret_key">Secret Key</x-ui.label>
                        <x-ui.input wire:model="paystack_secret_key" id="paystack_secret_key" type="password" />
                        <x-ui.error name="paystack_secret_key" />
                    </x-ui.field>

                    <x-ui.field>
                        <x-ui.label for="paystack_url">Base URL</x-ui.label>
                        <x-ui.input wire:model="paystack_url" id="paystack_url" />
                        <x-ui.error name="paystack_url" />
                    </x-ui.field>
                </div>
            </div>

            <!-- Epins -->
            <div class="space-y-6">
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white border-b border-neutral-100 dark:border-neutral-700 pb-2">Epins</h3>
                <div class="grid grid-cols-1 gap-6">
                    <x-ui.field>
                        <x-ui.label for="epins_api_key">API Key</x-ui.label>
                        <x-ui.input wire:model="epins_api_key" id="epins_api_key" type="password" />
                        <x-ui.error name="epins_api_key" />
                    </x-ui.field>

                    <x-ui.field>
                        <x-ui.label for="epins_url">Base URL</x-ui.label>
                        <x-ui.input wire:model="epins_url" id="epins_url" />
                        <x-ui.error name="epins_url" />
                    </x-ui.field>

                    <x-ui.field>
                        <x-ui.label for="epins_sandbox_url">Sandbox URL</x-ui.label>
                        <x-ui.input wire:model="epins_sandbox_url" id="epins_sandbox_url" />
                        <x-ui.error name="epins_sandbox_url" />
                    </x-ui.field>
                </div>
            </div>

            <!-- Dojah -->
            <div class="space-y-6">
                <h3 class="text-lg font-bold text-neutral-900 dark:text-white border-b border-neutral-100 dark:border-neutral-700 pb-2">Dojah</h3>
                <div class="grid grid-cols-1 gap-6">
                    <x-ui.field>
                        <x-ui.label for="dojah_api_key">API Key</x-ui.label>
                        <x-ui.input wire:model="dojah_api_key" id="dojah_api_key" type="password" />
                        <x-ui.error name="dojah_api_key" />
                    </x-ui.field>

                    <x-ui.field>
                        <x-ui.label for="dojah_app_id">App ID</x-ui.label>
                        <x-ui.input wire:model="dojah_app_id" id="dojah_app_id" />
                        <x-ui.error name="dojah_app_id" />
                    </x-ui.field>

                    <x-ui.field>
                        <x-ui.label for="dojah_base_url">Base URL</x-ui.label>
                        <x-ui.input wire:model="dojah_base_url" id="dojah_base_url" />
                        <x-ui.error name="dojah_base_url" />
                    </x-ui.field>
                </div>
            </div>

            <div class="flex items-center justify-end">
                <x-ui.button variant="primary" type="submit" class="w-full md:w-auto">
                    Save Changes
                </x-ui.button>
            </div>
        </form>
    </x-layouts.admin.settings>
</section>
