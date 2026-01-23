@props([
    'title' => $layoutSettings->homepage_faq_title,
    'description' => $layoutSettings->homepage_faq_description,
    'items' => $layoutSettings->homepage_faq_items,
])

<section class="py-20 px-6 bg-amber-500/[0.02] dark:bg-transparent relative overflow-hidden">
    <div class="absolute bottom-0 right-[-100px] w-[600px] h-[600px] bg-amber-500/5 blur-[100px] rounded-full pointer-events-none"></div>
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-16 reveal-active">
            <h2 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white mb-4">
                {{ $title }}
            </h2>
            <p class="text-lg text-slate-500 dark:text-slate-400">
                {{ $description }}
            </p>
        </div>

        <div class="space-y-4 reveal-active" style="transition-delay: 0.2s">
            <x-ui.accordion>
                @foreach($items as $index => $item)
                    <x-ui.accordion.item wire:key="faq-{{ $index }}" class="bg-gray-50/50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl overflow-hidden mb-4 shadow-sm">
                        <x-ui.accordion.trigger class="px-6 py-4 text-left font-bold text-slate-900 dark:text-neutral-100 hover:bg-primary/5 transition-colors">
                            {{ $item['question'] }}
                        </x-ui.accordion.trigger>
                        <x-ui.accordion.content class="px-6 pb-6 text-slate-600 dark:text-neutral-400 font-medium">
                            {{ $item['answer'] }}
                        </x-ui.accordion.content>
                    </x-ui.accordion.item>
                @endforeach
            </x-ui.accordion>
        </div>
    </div>
</section>
