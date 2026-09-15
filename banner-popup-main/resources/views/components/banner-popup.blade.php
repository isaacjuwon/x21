{{--
    x-banner-popup

    Drop this component into your main layout (e.g. app.blade.php), just before </body>.
    It injects the CSS, the JS config object, and the JS bundle automatically.

    Usage:
        <x-banner-popup />

    The component reads the current route name automatically.
    All configuration is pulled from config/banner-popup.php.
--}}
<link rel="stylesheet" href="{{ asset('vendor/banner-popup/css/banner-popup.css') }}">

<script>
window.BannerPopupConfig = {
    endpoint:     "{{ url(config('banner-popup.route_prefix', 'banner-popup') . '/active') }}",
    seenEndpoint: "{{ url(config('banner-popup.route_prefix', 'banner-popup')) }}",
    cookiePrefix: "{{ config('banner-popup.cookie_prefix', 'bp_') }}",
    currentPage:  "{{ optional(request()->route())->getName() }}",
};
</script>

<script src="{{ asset('vendor/banner-popup/js/banner-popup.js') }}" defer></script>
