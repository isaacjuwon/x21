@props(['header' => true, 'title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-neutral-950 text-slate-900 dark:text-white">
     <x-ui.layout.main>
            @if($header)
                <x-ui.layout.header class="fixed top-0 w-full z-50 px-6 py-6 border-none bg-transparent">
                    <div class="max-w-7xl mx-auto flex flex-1 justify-between items-center bg-white/90 dark:bg-neutral-900/90 backdrop-blur-md shadow-sm rounded-2xl px-6 py-3 border border-neutral-200 dark:border-neutral-800">
                        <a href="/" wire:navigate class="flex items-center">
                             <x-app-logo class="h-8" />
                        </a>
                        
                        <div class="flex items-center gap-4 ml-auto">
                            @guest
                                <x-ui.button variant="ghost" :href="route('login')" class="text-slate-600 dark:text-slate-400">Login</x-ui.button>
                                <x-ui.button variant="primary" :href="route('register')" class="rounded-xl px-6">
                                    Get Started
                                </x-ui.button>
                            @endguest

                            @auth
                                <x-ui.dropdown position="bottom-end">
                                    <x-slot:button class="justify-center">
                                        <x-ui.avatar size="sm" :src="auth()->user()->avatar_url" circle alt="Profile Picture" />
                                    </x-slot:button>

                                    <x-slot:menu class="w-56">
                                        <x-ui.dropdown.group :label="__('Signed in as')">
                                            <x-ui.dropdown.item>
                                                {{ auth()->user()->email }}
                                            </x-ui.dropdown.item>
                                        </x-ui.dropdown.group>

                                        <x-ui.dropdown.separator />

                                        <x-ui.dropdown.item :href="route('dashboard')" wire:navigate.live>
                                            Dashboard
                                        </x-ui.dropdown.item>

                                        <x-ui.dropdown.item :href="route('profile.edit')" wire:navigate.live>
                                            Account
                                        </x-ui.dropdown.item>

                                        <x-ui.dropdown.separator />

                                        <form action="{{ route('logout') }}" method="post" class="contents">
                                            @csrf
                                            <x-ui.dropdown.item as="button" type="submit">
                                                Sign Out
                                            </x-ui.dropdown.item>
                                        </form>
                                    </x-slot:menu>
                                </x-ui.dropdown>
                            @endauth

                            <x-ui.theme-switcher variant="inline" />
                        </div>
                    </div>
                </x-ui.layout.header>
            @endif

            <div class="p-6">
                {{ $slot }}
            </div>
        </x-ui.layout.main>

    @vite(['resources/js/app.js'])
    
    <!-- Ensure dark mode is applied after scripts load, this is also required to prevent flickering when many livewire component changes indepently -->
    <script>
        loadDarkMode()
    </script>
    </body>
</html>
