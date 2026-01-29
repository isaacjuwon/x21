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
                        'border-neutral-100 dark:border-neutral-800': !$theme.isLight
                    }"
                    class="p-6 bg-white rounded-[--radius-box] border-2 hover:border-primary transition-all cursor-pointer group shadow-sm flex-1"
                    role="button"
                    aria-pressed="false"
                    x-bind:aria-pressed="$theme.isLight"
                    aria-label="Activate light theme"
                >
                    <div class="flex flex-col items-center gap-4">
                        <div class="p-3 bg-neutral-900 rounded-[--radius-field] group-hover:bg-neutral-800 transition-colors">
                            <x-ui.icon name="sun" class="w-6 h-6 text-amber-400" />
                        </div>
                        <span class="text-xs font-bold text-neutral-900 uppercase tracking-widest">{{ __('Light') }}</span>
                    </div>
                </button>

                <!-- Dark Mode -->
                <button 
                    type="button"
                    x-on:click="$theme.setDark()"
                    x-bind:class="{
                        'ring-2 ring-primary border-primary': $theme.isDark,
                        'border-neutral-800': !$theme.isDark
                    }"
                    class="p-6 bg-neutral-900 rounded-[--radius-box] border-2 hover:border-primary transition-all cursor-pointer group shadow-sm flex-1"
                    role="button"
                    aria-pressed="false"
                    x-bind:aria-pressed="$theme.isDark"
                    aria-label="Activate dark theme"
                >
                    <div class="flex flex-col items-center gap-4">
                        <div class="p-3 bg-white rounded-[--radius-field] group-hover:bg-neutral-100 transition-colors">
                            <x-ui.icon name="moon" class="w-6 h-6 text-indigo-600" />
                        </div>
                        <span class="text-xs font-bold text-white uppercase tracking-widest">{{ __('Dark') }}</span>
                    </div>
                </button>

                <!-- System Mode -->
                <button 
                    type="button"
                    x-on:click="$theme.setSystem()"
                    x-bind:class="{
                        'ring-2 ring-primary border-primary': $theme.isSystem,
                        'border-neutral-100 dark:border-neutral-800': !$theme.isSystem
                    }"
                    class="p-6 bg-white dark:bg-neutral-900/50 rounded-[--radius-box] border-2 hover:border-primary transition-all cursor-pointer group shadow-sm flex-1"
                    role="button"
                    aria-pressed="false"
                    x-bind:aria-pressed="$theme.isSystem"
                    aria-label="Activate system theme"
                >
                    <div class="flex flex-col items-center gap-4">
                        <div class="p-3 bg-neutral-50 dark:bg-neutral-800 rounded-[--radius-field] group-hover:bg-neutral-100 dark:group-hover:bg-neutral-700 transition-colors">
                            <x-ui.icon name="computer-desktop" class="w-6 h-6 text-neutral-600 dark:text-neutral-400" />
                        </div>
                        <span class="text-xs font-bold text-neutral-900 dark:text-white uppercase tracking-widest">{{ __('System') }}</span>
                    </div>
                </button>
            </div>
        </div>
    </x-settings.layout>
</section>
