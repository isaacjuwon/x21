<x-layouts.admin.sidebar :title="$title ?? null">
   
        {{ $slot }}
    <x-ui.toast position="top-right" maxToasts="5" progressBarVariant="full" progressBarAlignment="bottom" />
</x-layouts.admin.sidebar>
