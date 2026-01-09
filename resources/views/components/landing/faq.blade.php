@props([
    'title' => $layoutSettings->homepage_faq_title,
    'description' => $layoutSettings->homepage_faq_description,
    'items' => $layoutSettings->homepage_faq_items,
])

<section class="py-20 px-6" x-data="{ visible: false }" x-intersect.once="visible = true">
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-16 reveal" :class="visible && 'reveal-active'">
            <h2 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white mb-4">
                {{ $title }}
            </h2>
            <p class="text-lg text-slate-500 dark:text-slate-400">
                {{ $description }}
            </p>
        </div>

        <div class="space-y-4 reveal" :class="visible && 'reveal-active'" style="transition-delay: 0.2s">
            <x-ui.accordion>
                @foreach($items as $index => $item)
                    <x-ui.accordion.item wire:key="faq-{{ $index }}" class="glass border-white/5 rounded-2xl overflow-hidden mb-4">
                        <x-ui.accordion.trigger class="px-6 py-4 text-left font-bold text-slate-900 dark:text-white hover:bg-white/5">
                            {{ $item['question'] }}
                        </x-ui.accordion.trigger>
                        <x-ui.accordion.content class="px-6 pb-6 text-slate-600 dark:text-slate-400 font-medium">
                            {{ $item['answer'] }}
                        </x-ui.accordion.content>
                    </x-ui.accordion.item>
                @endforeach
            </x-ui.accordion>
        </div>
    </div>
</section>
