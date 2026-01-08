@props([
    'title' => $layoutSettings->homepage_faq_title,
    'description' => $layoutSettings->homepage_faq_description,
    'items' => $layoutSettings->homepage_faq_items,
])

<section class="py-12 bg-white dark:bg-zinc-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-extrabold text-zinc-900 dark:text-white sm:text-4xl">
                {{ $title }}
            </h2>
            <p class="mt-4 text-lg text-zinc-500 dark:text-zinc-400">
                {{ $description }}
            </p>
        </div>

        <div class="max-w-3xl mx-auto">
            <x-ui.accordion>
                @foreach($items as $index => $item)
                    <x-ui.accordion.item wire:key="faq-{{ $index }}">
                        <x-ui.accordion.trigger>
                            {{ $item['question'] }}
                        </x-ui.accordion.trigger>
                        <x-ui.accordion.content>
                            <p class="text-zinc-600 dark:text-zinc-300">
                                {{ $item['answer'] }}
                            </p>
                        </x-ui.accordion.content>
                    </x-ui.accordion.item>
                @endforeach
            </x-ui.accordion>
        </div>
    </div>
</section>
