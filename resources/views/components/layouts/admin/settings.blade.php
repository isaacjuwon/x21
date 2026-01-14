<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <x-ui.navlist>
            <x-ui.navlist.item :href="route('admin.settings.general')" :label="__('General')" :active="request()->routeIs('admin.settings.general')" wire:navigate />
            <x-ui.navlist.item :href="route('admin.settings.shares')" :label="__('Shares')" :active="request()->routeIs('admin.settings.shares')" wire:navigate />
            <x-ui.navlist.item :href="route('admin.settings.loans')" :label="__('Loans')" :active="request()->routeIs('admin.settings.loans')" wire:navigate />
            <x-ui.navlist.item :href="route('admin.settings.wallet')" :label="__('Wallet')" :active="request()->routeIs('admin.settings.wallet')" wire:navigate />
            <x-ui.navlist.item :href="route('admin.settings.layout')" :label="__('Layout')" :active="request()->routeIs('admin.settings.layout')" wire:navigate />
            <x-ui.navlist.item :href="route('admin.settings.integrations')" :label="__('Integrations')" :active="request()->routeIs('admin.settings.integrations')" wire:navigate />
            <x-ui.navlist.item :href="route('admin.settings.verification')" :label="__('Verification')" :active="request()->routeIs('admin.settings.verification')" wire:navigate />
        </x-ui.navlist>
    </div>

    <x-ui.separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <x-ui.heading>{{ $heading ?? '' }}</x-ui.heading>
        <x-ui.description>{{ $subheading ?? '' }}</x-ui.description>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
