<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <x-ui.header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <x-ui.sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <a href="{{ route('dashboard') }}" class="ms-2 me-5 flex items-center space-x-2 rtl:space-x-reverse lg:ms-0" wire:navigate>
                <x-app-logo />
            </a>

            <x-ui.navbar class="-mb-px max-lg:hidden">
                <x-ui.navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </x-ui.navbar.item>
            </x-ui.navbar>

            <x-ui.spacer />

            <x-ui.navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
                <x-ui.tooltip :content="__('Search')" position="bottom">
                    <x-ui.navbar.item class="!h-10 [&>div>svg]:size-5" icon="magnifying-glass" href="#" :label="__('Search')" />
                </x-ui.tooltip>
                <x-ui.tooltip :content="__('Repository')" position="bottom">
                    <x-ui.navbar.item
                        class="h-10 max-lg:hidden [&>div>svg]:size-5"
                        icon="folder-git-2"
                        href="https://github.com/laravel/livewire-starter-kit"
                        target="_blank"
                        :label="__('Repository')"
                    />
                </x-ui.tooltip>
                <x-ui.tooltip :content="__('Documentation')" position="bottom">
                    <x-ui.navbar.item
                        class="h-10 max-lg:hidden [&>div>svg]:size-5"
                        icon="book-open-text"
                        href="https://laravel.com/docs/starter-kits#livewire"
                        target="_blank"
                        label="Documentation"
                    />
                </x-ui.tooltip>
            </x-ui.navbar>

            <!-- Desktop User Menu -->
            <x-ui.dropdown position="top" align="end">
                <x-ui.profile
                    class="cursor-pointer"
                    :initials="auth()->user()->initials()"
                />

                <x-ui.menu>
                    <x-ui.menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </x-ui.menu.radio.group>

                    <x-ui.menu.separator />

                    <x-ui.menu.radio.group>
                        <x-ui.menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</x-ui.menu.item>
                    </x-ui.menu.radio.group>

                    <x-ui.menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <x-ui.menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                            {{ __('Log Out') }}
                        </x-ui.menu.item>
                    </form>
                </x-ui.menu>
            </x-ui.dropdown>
        </x-ui.header>

        <!-- Mobile Menu -->
        <x-ui.sidebar stashable sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <x-ui.sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="ms-1 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <x-ui.navlist variant="outline">
                <x-ui.navlist.group :heading="__('Platform')">
                    <x-ui.navlist.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                    </x-ui.navlist.item>
                </x-ui.navlist.group>
            </x-ui.navlist>

            <x-ui.spacer />

            <x-ui.navlist variant="outline">
                <x-ui.navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                {{ __('Repository') }}
                </x-ui.navlist.item>

                <x-ui.navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                {{ __('Documentation') }}
                </x-ui.navlist.item>
            </x-ui.navlist>
        </x-ui.sidebar>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
