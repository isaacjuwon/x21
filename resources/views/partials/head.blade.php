<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

@if($generalSettings->site_favicon)
    <link rel="icon" href="{{ Storage::url($generalSettings->site_favicon) }}" sizes="any">
@else
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
@endif

@if($generalSettings->site_dark_favicon)
    <link rel="icon" href="{{ Storage::url($generalSettings->site_dark_favicon) }}" media="(prefers-color-scheme: dark)">
@endif

<link rel="apple-touch-icon" href="{{ $generalSettings->site_favicon ? Storage::url($generalSettings->site_favicon) : '/apple-touch-icon.png' }}">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css'])
 <script>
        // Load dark mode before page renders to prevent flicker
        const loadDarkMode = () => {
            const theme = localStorage.getItem('theme') ?? 'system'
            
            if (
                theme === 'dark' ||
                (theme === 'system' &&
                    window.matchMedia('(prefers-color-scheme: dark)')
                    .matches)
            ) {
                document.documentElement.classList.add('dark')
            }
        }
                
        // Initialize on page load
        loadDarkMode();
        
        // Reinitialize after Livewire navigation (for spa mode)
        document.addEventListener('livewire:navigated', function() {
            loadDarkMode();
        });
    </script>


