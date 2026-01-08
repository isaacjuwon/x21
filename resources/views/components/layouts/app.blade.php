<x-layouts.app.sidebar :title="$title ?? null">
    <x-ui.main>
        {{ $slot }}
    </x-ui.main>
</x-layouts.app.sidebar>
