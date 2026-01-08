@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden']) }}>
    @if ($title || $description)
        <div class="px-6 py-4 border-b border-zinc-100 dark:border-zinc-800/50">
            @if ($title)
                <h3 class="text-base font-semibold text-zinc-900 dark:text-white leading-6">
                    {{ $title }}
                </h3>
            @endif
            @if ($description)
                <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ $description }}
                </div>
            @endif
        </div>
    @endif

    <div class="p-6 flex-1 min-h-[300px]">
        {{ $slot }}
    </div>
</div>
