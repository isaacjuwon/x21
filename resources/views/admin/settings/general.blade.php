<?php

use App\Livewire\Concerns\HasToast;
use App\Settings\GeneralSettings;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use HasToast;
    use WithFileUploads;

    public string $site_name;
    public string $site_description;
    public string $contact_email;
    public string $support_email;
    public bool $maintenance_mode;
    public bool $registration_enabled;
    public string $currency;
    public string $timezone;

    public $site_logo;
    public $site_dark_logo;
    public $site_favicon;
    public $site_dark_favicon;

    public $site_logo_path;
    public $site_dark_logo_path;
    public $site_favicon_path;
    public $site_dark_favicon_path;

    public function mount(GeneralSettings $settings): void
    {
        $this->site_name = $settings->site_name;
        $this->site_description = $settings->site_description;
        $this->contact_email = $settings->contact_email;
        $this->support_email = $settings->support_email;
        $this->maintenance_mode = $settings->maintenance_mode;
        $this->registration_enabled = $settings->registration_enabled;
        $this->currency = $settings->currency;
        $this->timezone = $settings->timezone;

        $this->site_logo_path = $settings->site_logo;
        $this->site_dark_logo_path = $settings->site_dark_logo;
        $this->site_favicon_path = $settings->site_favicon;
        $this->site_dark_favicon_path = $settings->site_dark_favicon;
    }

    public function save(GeneralSettings $settings)
    {
        $settings->site_name = $this->site_name;
        $settings->site_description = $this->site_description;
        $settings->contact_email = $this->contact_email;
        $settings->support_email = $this->support_email;
        $settings->maintenance_mode = $this->maintenance_mode;
        $settings->registration_enabled = $this->registration_enabled;
        $settings->currency = $this->currency;
        $settings->timezone = $this->timezone;

        if ($this->site_logo) {
            $settings->site_logo = $this->site_logo->store('settings', 'public');
            $this->site_logo_path = $settings->site_logo;
        }

        if ($this->site_dark_logo) {
            $settings->site_dark_logo = $this->site_dark_logo->store('settings', 'public');
            $this->site_dark_logo_path = $settings->site_dark_logo;
        }

        if ($this->site_favicon) {
            $settings->site_favicon = $this->site_favicon->store('settings', 'public');
            $this->site_favicon_path = $settings->site_favicon;
        }

        if ($this->site_dark_favicon) {
            $settings->site_dark_favicon = $this->site_dark_favicon->store('settings', 'public');
            $this->site_dark_favicon_path = $settings->site_dark_favicon;
        }

        $settings->save();

        $this->toastSuccess('General settings updated successfully.');
    }

    public function render()
    {
        return $this->view()
            ->title('General Settings')
            ->layout('layouts::admin');
    }
};
?>

<section class="w-full">
    <x-layouts.admin.settings heading="General Settings" subheading="Configure site-wide application settings">
        <form wire:submit="save" class="mx-6 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Branding Section -->
                <div class="md:col-span-2 space-y-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b pb-2">Branding</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Light Logo -->
                        <x-ui.field>
                            <x-ui.label>Light Logo</x-ui.label>
                            <div class="mt-2 flex flex-col items-center gap-4">
                                @if ($site_logo)
                                    <img src="{{ $site_logo->temporaryUrl() }}" class="h-16 object-contain bg-gray-50 p-2 rounded border" />
                                @elseif ($site_logo_path)
                                    <img src="{{ Storage::url($site_logo_path) }}" class="h-16 object-contain bg-gray-50 p-2 rounded border" />
                                @endif
                                <x-ui.input type="file" wire:model="site_logo" accept="image/*" />
                            </div>
                            <x-ui.error name="site_logo" />
                        </x-ui.field>

                        <!-- Dark Logo -->
                        <x-ui.field>
                            <x-ui.label>Dark Logo</x-ui.label>
                            <div class="mt-2 flex flex-col items-center gap-4">
                                @if ($site_dark_logo)
                                    <img src="{{ $site_dark_logo->temporaryUrl() }}" class="h-16 object-contain bg-gray-900 p-2 rounded border" />
                                @elseif ($site_dark_logo_path)
                                    <img src="{{ Storage::url($site_dark_logo_path) }}" class="h-16 object-contain bg-gray-900 p-2 rounded border" />
                                @endif
                                <x-ui.input type="file" wire:model="site_dark_logo" accept="image/*" />
                            </div>
                            <x-ui.error name="site_dark_logo" />
                        </x-ui.field>

                        <!-- Favicon -->
                        <x-ui.field>
                            <x-ui.label>Favicon</x-ui.label>
                            <div class="mt-2 flex flex-col items-center gap-4">
                                @if ($site_favicon)
                                    <img src="{{ $site_favicon->temporaryUrl() }}" class="h-12 w-12 object-contain bg-gray-50 p-2 rounded border" />
                                @elseif ($site_favicon_path)
                                    <img src="{{ Storage::url($site_favicon_path) }}" class="h-12 w-12 object-contain bg-gray-50 p-2 rounded border" />
                                @endif
                                <x-ui.input type="file" wire:model="site_favicon" accept="image/*" />
                            </div>
                            <x-ui.error name="site_favicon" />
                        </x-ui.field>

                        <!-- Dark Favicon -->
                        <x-ui.field>
                            <x-ui.label>Dark Favicon</x-ui.label>
                            <div class="mt-2 flex flex-col items-center gap-4">
                                @if ($site_dark_favicon)
                                    <img src="{{ $site_dark_favicon->temporaryUrl() }}" class="h-12 w-12 object-contain bg-gray-900 p-2 rounded border" />
                                @elseif ($site_dark_favicon_path)
                                    <img src="{{ Storage::url($site_dark_favicon_path) }}" class="h-12 w-12 object-contain bg-gray-900 p-2 rounded border" />
                                @endif
                                <x-ui.input type="file" wire:model="site_dark_favicon" accept="image/*" />
                            </div>
                            <x-ui.error name="site_dark_favicon" />
                        </x-ui.field>
                    </div>
                </div>

                <!-- Site Information -->
                <div class="md:col-span-2 space-y-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b pb-2">Site Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-ui.field>
                            <x-ui.label for="site_name">Site Name</x-ui.label>
                            <x-ui.input wire:model="site_name" id="site_name" />
                            <x-ui.error name="site_name" />
                        </x-ui.field>

                        <x-ui.field>
                            <x-ui.label for="currency">Currency</x-ui.label>
                            <x-ui.input wire:model="currency" id="currency" />
                            <x-ui.error name="currency" />
                        </x-ui.field>

                        <x-ui.field class="md:col-span-2">
                            <x-ui.label for="site_description">Site Description</x-ui.label>
                            <x-ui.textarea wire:model="site_description" id="site_description" rows="3" />
                            <x-ui.error name="site_description" />
                        </x-ui.field>

                        <x-ui.field>
                            <x-ui.label for="contact_email">Contact Email</x-ui.label>
                            <x-ui.input wire:model="contact_email" id="contact_email" type="email" />
                            <x-ui.error name="contact_email" />
                        </x-ui.field>

                        <x-ui.field>
                            <x-ui.label for="support_email">Support Email</x-ui.label>
                            <x-ui.input wire:model="support_email" id="support_email" type="email" />
                            <x-ui.error name="support_email" />
                        </x-ui.field>

                        <x-ui.field>
                            <x-ui.label for="timezone">Timezone</x-ui.label>
                            <x-ui.input wire:model="timezone" id="timezone" />
                            <x-ui.error name="timezone" />
                        </x-ui.field>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white border-b pb-2">Settings</h3>
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <x-ui.checkbox wire:model="maintenance_mode" id="maintenance_mode" />
                        <x-ui.label for="maintenance_mode" class="mb-0">Maintenance Mode</x-ui.label>
                    </div>

                    <div class="flex items-center gap-2">
                        <x-ui.checkbox wire:model="registration_enabled" id="registration_enabled" />
                        <x-ui.label for="registration_enabled" class="mb-0">Enable Registration</x-ui.label>
                    </div>
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
