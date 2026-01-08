<?php

use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view()
            ->title('Appearance Settings')
            ->layout('layouts::app');
    }
};
?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Appearance')" :subheading="__('Update the appearance settings for your account')">
        <div class="my-6 w-full space-y-6" x-data>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-4">
                <!-- Light Mode -->
                <button 
                    type="button"
                    x-on:click="$theme.setLight()"
                    x-bind:class="{
                        'ring-2 ring-primary border-primary': $theme.isLight,
                        'border-gray-300': !$theme.isLight
                    }"
                    class="p-6 bg-white rounded-xl border-2 hover:border-primary transition-all cursor-pointer group shadow-sm"
                    role="button"
                    aria-pressed="false"
                    x-bind:aria-pressed="$theme.isLight"
                    aria-label="Activate light theme"
                >
                    <div class="flex flex-col items-center gap-3">
                        <div class="p-3 bg-gray-800 rounded-lg group-hover:bg-gray-700 transition-colors">
                            <x-ui.icon name="sun" class="w-6 h-6 text-amber-400" />
                        </div>
                        <span class="font-medium text-gray-900">{{ __('Light') }}</span>
                        <span class="text-sm text-gray-600 text-center">{{ __('Light theme') }}</span>
                    </div>
                </button>

                <!-- Dark Mode -->
                <button 
                    type="button"
                    x-on:click="$theme.setDark()"
                    x-bind:class="{
                        'ring-2 ring-primary border-primary': $theme.isDark,
                        'border-gray-700': !$theme.isDark
                    }"
                    class="p-6 bg-gray-900 rounded-xl border-2 hover:border-primary transition-all cursor-pointer group shadow-sm"
                    role="button"
                    aria-pressed="false"
                    x-bind:aria-pressed="$theme.isDark"
                    aria-label="Activate dark theme"
                >
                    <div class="flex flex-col items-center gap-3">
                        <div class="p-3 bg-white rounded-lg group-hover:bg-gray-100 transition-colors">
                            <x-ui.icon name="moon" class="w-6 h-6 text-indigo-600" />
                        </div>
                        <span class="font-medium text-white">{{ __('Dark') }}</span>
                        <span class="text-sm text-gray-400 text-center">{{ __('Dark theme') }}</span>
                    </div>
                </button>

                <!-- System Mode -->
                <button 
                    type="button"
                    x-on:click="$theme.setSystem()"
                    x-bind:class="{
                        'ring-2 ring-primary border-primary': $theme.isSystem,
                        'border-gray-200 dark:border-gray-700': !$theme.isSystem
                    }"
                    class="p-6 bg-white dark:bg-gray-800 rounded-xl border-2 hover:border-primary transition-all cursor-pointer group shadow-sm"
                    role="button"
                    aria-pressed="false"
                    x-bind:aria-pressed="$theme.isSystem"
                    aria-label="Activate system theme"
                >
                    <div class="flex flex-col items-center gap-3">
                        <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg group-hover:bg-gray-100 dark:group-hover:bg-gray-600 transition-colors">
                            <x-ui.icon name="computer-desktop" class="w-6 h-6 text-gray-600 dark:text-gray-400" />
                        </div>
                        <span class="font-medium text-gray-900 dark:text-white">{{ __('System') }}</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400 text-center">{{ __('Follow system') }}</span>
                    </div>
                </button>
            </div>
        </div>
    </x-settings.layout>
</section>
