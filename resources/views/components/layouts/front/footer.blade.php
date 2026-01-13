@use('App\Settings\LayoutSettings')
@php
    $layoutSettings = app(LayoutSettings::class);
@endphp

<footer class="py-12 border-t border-border">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-8">
            <x-app-logo class="h-6 opacity-60" />
            
            <div class="flex flex-col items-center md:items-start gap-4">
                @foreach(\App\Models\Page::all() as $page)
                    <a href="{{ route('pages.show', $page) }}" class="text-sm font-medium text-foreground-content hover:text-primary transition-colors">{{ $page->title }}</a>
                @endforeach
            </div>

            <div class="flex items-center gap-6">
                 @if($layoutSettings->facebook)
                    <a href="{{ $layoutSettings->facebook }}" class="text-foreground-content hover:text-primary transition-colors" target="_blank">
                        <x-ui.icon name="ps:facebook-logo" class="size-5" />
                    </a>
                @endif
                @if($layoutSettings->twitter)
                    <a href="{{ $layoutSettings->twitter }}" class="text-foreground-content hover:text-primary transition-colors" target="_blank">
                        <x-ui.icon name="ps:twitter-logo" class="size-5" />
                    </a>
                @endif
                 @if($layoutSettings->email)
                    <a href="mailto:{{ $layoutSettings->email }}" class="text-foreground-content hover:text-primary transition-colors">
                        <x-ui.icon name="envelope" class="size-5" />
                    </a>
                @endif
            </div>
        </div>
        <div class="mt-12 text-center">
            <p class="text-xs text-foreground-content font-medium opacity-50">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</footer>
