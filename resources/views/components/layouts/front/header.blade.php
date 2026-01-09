@props(['header' => true, 'title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-200 dark:bg-zinc-800">
     <x-ui.layout.main>
            @if($header)
                <x-ui.layout.header>
                    <x-slot:brand>
                        <a href="{{ route('home') }}" class="flex items-center gap-2">
                            <x-app-logo />
                        </a>
                    </x-slot:brand>
                    <x-ui.sidebar.toggle class="md:hidden"/>
                    <x-ui.navbar class="flex-1 hidden lg:flex">
                        <x-ui.navbar.item
                            icon="home"
                            label="Home" 
                            href="{{ route('home') }}"
                        />

                        @foreach(\App\Models\Page::published()->get() as $page)
                            <x-ui.navbar.item 
                                :label="$page->title" 
                                href="{{ route('pages.show', $page) }}"                    
                            />
                        @endforeach
                    </x-ui.navbar>

                    <div class="flex ml-auto gap-x-3 items-center">
                        @guest
                            <x-ui.button variant="ghost" :href="route('login')">Log in</x-ui.button>
                            <x-ui.button variant="primary" :href="route('register')">Register</x-ui.button>
                        @endguest

                        @auth
                            <x-ui.dropdown position="bottom-end">
                                <x-slot:button class="justify-center">
                                    <x-ui.avatar size="sm" src="/iman.png" circle alt="Profile Picture" />
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
                </x-ui.layout.header>
            @endif

            <div class="p-6">
                {{ $slot }}
            </div>
        </x-ui.layout.main>

        @livewireScripts
    </body>
</html>
