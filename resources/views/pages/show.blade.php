<x-layouts::front>
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-neutral-800 overflow-hidden shadow-sm rounded-[--radius-box]">
            <div class="p-6 text-neutral-900 dark:text-white">
                <h1 class="text-2xl font-bold mb-6">{{ $page->title }}</h1>
                
                <div class="prose dark:prose-invert max-w-none prose-neutral">
                    {!! Str::markdown($page->content ?? '') !!}
                </div>
            </div>
        </div>
    </div>
    <x-layouts.front.footer />
</x-layouts::front>
