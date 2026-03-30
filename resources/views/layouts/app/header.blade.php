<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

            <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:navbar.item>
            </flux:navbar>

            <flux:spacer />

            <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
                <flux:tooltip :content="__('Search')" position="bottom">
                    <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="magnifying-glass" href="#" :label="__('Search')" />
                </flux:tooltip>
            </flux:navbar>

            <x-desktop-user-menu />
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="wallet" :href="route('wallet.index')" :current="request()->routeIs('wallet.*')" wire:navigate>
                        {{ __('Wallet') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="banknotes" :href="route('loan.index')" :current="request()->routeIs('loan.*')" wire:navigate>
                        {{ __('Loans') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="chart-bar" :href="route('shares.index')" :current="request()->routeIs('shares.*')" wire:navigate>
                        {{ __('Shares') }}
                    </flux:sidebar.item>

                    <flux:sidebar.group expandable heading="{{ __('Services') }}" icon="layout-grid" :current="request()->routeIs('services.*')">
                        <flux:sidebar.item :href="route('services.airtime')" :current="request()->routeIs('services.airtime')" wire:navigate>{{ __('Airtime') }}</flux:sidebar.item>
                        <flux:sidebar.item :href="route('services.data')" :current="request()->routeIs('services.data')" wire:navigate>{{ __('Data') }}</flux:sidebar.item>
                        <flux:sidebar.item :href="route('services.cable')" :current="request()->routeIs('services.cable')" wire:navigate>{{ __('Cable TV') }}</flux:sidebar.item>
                        <flux:sidebar.item :href="route('services.electricity')" :current="request()->routeIs('services.electricity')" wire:navigate>{{ __('Electricity') }}</flux:sidebar.item>
                        <flux:sidebar.item :href="route('services.education')" :current="request()->routeIs('services.education')" wire:navigate>{{ __('Education') }}</flux:sidebar.item>
                    </flux:sidebar.group>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Account')" class="grid">
                    <flux:sidebar.item icon="user" :href="route('profile.edit')" :current="request()->routeIs('profile.*')" wire:navigate>
                        {{ __('My Profile') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="cog" :href="route('appearance.edit')" :current="request()->routeIs('appearance')" wire:navigate>
                        {{ __('Appearance') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />
        </flux:sidebar>

        {{ $slot }}

        <flux:toast />

        @fluxScripts
    </body>
</html>
