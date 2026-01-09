@props(['header' => true])
<x-layouts.front.header :title="$title ?? null" :header="$header">
   
        {{ $slot }}
        <x-ui.toast position="top-right" maxToasts="5" progressBarVariant="full" progressBarAlignment="bottom" />
</x-layouts.front.header>
