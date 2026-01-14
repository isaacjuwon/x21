<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Maintenance Mode</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900 bg-gray-50 dark:bg-gray-950 dark:text-white h-screen flex flex-col items-center justify-center p-4">
    <div class="text-center max-w-lg">
        <div class="mb-8 flex justify-center">
            @php
                $settings = app(\App\Settings\GeneralSettings::class);
            @endphp
            
            @if($settings->site_logo)
                <img src="{{ Storage::url($settings->site_logo) }}" alt="{{ config('app.name') }}" class="h-16 w-auto" />
            @else
                <x-app-logo class="h-16 w-auto" />
            @endif
        </div>

        <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl mb-4">
            Under Maintenance
        </h1>
        
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
            We are currently performing scheduled maintenance. We'll be back online shortly. Thank you for your patience.
        </p>

        <div class="flex items-center justify-center gap-4">
            <a href="{{ url()->current() }}" class="text-sm font-semibold text-primary-600 hover:text-primary-500 dark:text-primary-400">
                Reload Page &rarr;
            </a>
            
            <a href="mailto:{{ $settings->support_email }}" class="text-sm font-semibold text-gray-900 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white">
                Contact Support
            </a>
        </div>
    </div>
</body>
</html>
