<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
        <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 not-has-[nav]:hidden">
            @if (Route::has('login'))
                <nav class="flex items-center justify-end gap-4">
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal"
                        >
                            Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] text-[#1b1b18] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal"
                        >
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal">
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>

        <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
            <main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row shadow-xl rounded-xl overflow-hidden">
                <div class="flex-1 p-6 pb-12 lg:p-20 bg-white dark:bg-[#161615] dark:text-[#EDEDEC]">
                    <h1 class="text-4xl font-bold mb-4">{{ $layoutSettings->homepage_title }}</h1>
                    <p class="text-lg text-[#706f6c] dark:text-[#A1A09A] mb-8">{{ $layoutSettings->homepage_description }}</p>

                    <div class="mt-12">
                        <h2 class="text-2xl font-semibold mb-6">{{ $layoutSettings->homepage_features_title }}</h2>
                        <p class="text-[#706f6c] dark:text-[#A1A09A] mb-8">{{ $layoutSettings->homepage_features_description }}</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($layoutSettings->homepage_features_items as $feature)
                                <div class="p-4 rounded-lg border border-zinc-200 dark:border-zinc-800">
                                    <h3 class="font-medium mb-2">{{ $feature['title'] }}</h3>
                                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $feature['description'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="lg:w-1/3 bg-zinc-50 dark:bg-zinc-900 p-12 flex flex-col justify-center items-center text-center border-s border-zinc-200 dark:border-zinc-800">
                    @if($generalSettings->site_logo)
                        <img src="{{ $generalSettings->site_logo }}" alt="{{ $generalSettings->site_name }}" class="h-24 w-auto mb-8">
                    @else
                        <x-app-logo class="h-24 w-auto mb-8" />
                    @endif

                    <h2 class="text-2xl font-bold mb-4">{{ $generalSettings->site_name }}</h2>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-8">{{ $generalSettings->site_description }}</p>

                    <div class="flex gap-4">
                        @if($layoutSettings->facebook)
                            <a href="{{ $layoutSettings->facebook }}" class="text-zinc-400 hover:text-primary-color transition-colors">
                                <flux:icon.facebook class="size-6" />
                            </a>
                        @endif
                        @if($layoutSettings->twitter)
                            <a href="{{ $layoutSettings->twitter }}" class="text-zinc-400 hover:text-primary-color transition-colors">
                                <flux:icon.twitter class="size-6" />
                            </a>
                        @endif
                        @if($layoutSettings->instagram)
                            <a href="{{ $layoutSettings->instagram }}" class="text-zinc-400 hover:text-primary-color transition-colors">
                                <flux:icon.instagram class="size-6" />
                            </a>
                        @endif
                    </div>
                </div>
            </main>
        </div>

        <footer class="mt-12 text-sm text-[#706f6c] dark:text-[#A1A09A]">
            &copy; {{ date('Y') }} {{ $generalSettings->site_name }}. {{ __('All rights reserved.') }}
        </footer>
    </body>
</html>
