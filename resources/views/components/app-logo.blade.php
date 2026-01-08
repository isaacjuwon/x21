<div class="flex aspect-square size-8 items-center justify-center rounded-md overflow-hidden">
    @if($generalSettings->site_logo || $generalSettings->site_dark_logo)
        @if($generalSettings->site_logo)
            <img src="{{ Storage::url($generalSettings->site_logo) }}" alt="{{ $generalSettings->site_name }}" class="h-full w-full object-contain dark:hidden">
        @endif
        @if($generalSettings->site_dark_logo)
            <img src="{{ Storage::url($generalSettings->site_dark_logo) }}" alt="{{ $generalSettings->site_name }}" class="hidden h-full w-full object-contain dark:block">
        @else
            @if($generalSettings->site_logo)
                <img src="{{ Storage::url($generalSettings->site_logo) }}" alt="{{ $generalSettings->site_name }}" class="hidden h-full w-full object-contain dark:block">
            @endif
        @endif
    @else
        <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-primary-600 text-white">
            <span class="font-bold text-lg">{{ substr($generalSettings->site_name, 0, 1) }}</span>
        </div>
    @endif
</div>
<div class="ms-1 grid flex-1 text-start text-sm">
    <span class="mb-0.5 truncate leading-tight font-semibold">{{ $generalSettings->site_name }}</span>
</div>
