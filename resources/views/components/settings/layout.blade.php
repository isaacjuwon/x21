<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <x-ui.navlist>
            <x-ui.navlist.item :href="route('profile.edit')" :label="__('Profile')" wire:navigate />
            <x-ui.navlist.item :href="route('user-password.edit')" :label="__('Password')" wire:navigate />
            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <x-ui.navlist.item :href="route('two-factor.show')" :label="__('Two-Factor Auth')" wire:navigate />
            @endif
            <x-ui.navlist.item :href="route('appearance.edit')" :label="__('Appearance')" wire:navigate />
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
