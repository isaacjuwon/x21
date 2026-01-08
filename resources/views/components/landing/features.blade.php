@props([
    'title' => $layoutSettings->homepage_features_title,
    'description' => $layoutSettings->homepage_features_description,
    'items' => $layoutSettings->homepage_features_items,
])

<section class="py-12 bg-zinc-50 dark:bg-zinc-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-extrabold text-zinc-900 dark:text-white sm:text-4xl">
                {{ $title }}
            </h2>
            <p class="mt-4 text-lg text-zinc-500 dark:text-zinc-400">
                {{ $description }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($items as $item)
                <div class="bg-white dark:bg-zinc-900 overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-zinc-900 dark:text-white">
                            {{ $item['title'] }}
                        </h3>
                        <div class="mt-2 text-base text-zinc-500 dark:text-zinc-400">
                            {{ $item['description'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
