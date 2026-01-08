<x-layouts::front>
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-zinc-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-zinc-900 dark:text-zinc-100">
                <h1 class="text-3xl font-bold mb-6">{{ $page->title }}</h1>
                
                <div class="prose dark:prose-invert max-w-none">
                    {!! Str::markdown($page->content ?? '') !!}
                </div>
            </div>
        </div>
    </div>
</x-layouts::front>
