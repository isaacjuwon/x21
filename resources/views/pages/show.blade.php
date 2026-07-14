<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        @include('partials.head')
        <title>{{ $page->title }} - {{ config('app.name', 'Laravel') }}</title>
    </head>
    <body class="bg-white dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 antialiased">
        <!-- Navbar -->
        <nav class="sticky top-0 z-50 bg-white/80 dark:bg-zinc-950/80 backdrop-blur-md border-b border-zinc-200 dark:border-zinc-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('home') }}" class="flex items-center gap-2">
                            <x-app-logo class="h-8 w-auto text-primary-color" />
                            <span class="text-xl font-bold tracking-tight">{{ config('app.name', 'Laravel') }}</span>
                        </a>
                    </div>

                    <div class="flex items-center gap-4">
                        @auth
                            <flux:button href="{{ route('dashboard') }}" variant="primary" size="sm" wire:navigate>
                                Dashboard
                            </flux:button>
                        @else
                            <flux:button href="{{ route('login') }}" variant="ghost" size="sm" wire:navigate>
                                Log in
                            </flux:button>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <h1 class="text-4xl font-extrabold mb-8">{{ $page->title }}</h1>
            
            <div class="prose prose-zinc dark:prose-invert max-w-none">
                {!! $page->content !!}
            </div>
        </main>
        
        <footer class="mt-12 text-center text-sm text-zinc-500 pb-8">
            <a href="{{ route('home') }}" class="hover:text-primary-color">&larr; Back to Home</a>
        </footer>
    </body>
</html>
