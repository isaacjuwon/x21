@php
    $generalSettings = app(App\Settings\GeneralSettings::class);
    $siteName = $generalSettings->site_name ?? 'Laravel Starter Kit';
    $siteLogo = $generalSettings->site_logo;
    $siteDarkLogo = $generalSettings->site_dark_logo ?? $siteLogo;
@endphp

@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground overflow-hidden">
            @if($siteLogo)
                <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="h-8 w-auto dark:hidden">
                <img src="{{ $siteDarkLogo }}" alt="{{ $siteName }}" class="h-8 w-auto hidden dark:block">
            @else
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
            @endif
        </x-slot>
        <span class="hidden lg:block">{{ $siteName }}</span>
    </flux:sidebar.brand>
@else
    <flux:brand {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground overflow-hidden">
            @if($siteLogo)
                <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="h-8 w-auto dark:hidden">
                <img src="{{ $siteDarkLogo }}" alt="{{ $siteName }}" class="h-8 w-auto hidden dark:block">
            @else
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
            @endif
        </x-slot>
        <span class="hidden lg:block">{{ $siteName }}</span>
    </flux:brand>
@endif
