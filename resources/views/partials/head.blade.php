<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.$generalSettings->site_name : $generalSettings->site_name }}
</title>

@if($generalSettings->site_favicon)
    <link rel="icon" href="{{ $generalSettings->site_favicon }}" sizes="any">
@else
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
@endif

<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family={{ str($layoutSettings->font_family)->slug() }}:400,500,600" rel="stylesheet" />

<style>
    :root {
        --primary-color: {{ $layoutSettings->primary_color }};
        --font-family: '{{ $layoutSettings->font_family }}', sans-serif;
    }
    body {
        font-family: var(--font-family);
    }
</style>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
