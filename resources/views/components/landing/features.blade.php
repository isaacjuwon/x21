@props([
    'title' => $layoutSettings->homepage_features_title,
    'description' => $layoutSettings->homepage_features_description,
    'items' => $layoutSettings->homepage_features_items,
])

<section class="py-20 px-6" x-data="{ visible: false }" x-intersect.once="visible = true">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16 reveal" :class="visible && 'reveal-active'">
            <h2 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-transparent bg-clip-text bg-linear-to-r from-primary to-accent mb-4">
                {{ $title }}
            </h2>
            <p class="text-lg text-slate-500 dark:text-slate-400 max-w-2xl mx-auto">
                {{ $description }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($items as $index => $item)
                <div class="glass p-8 rounded-[2rem] border-white/5 reveal group hover:border-primary/30 transition-colors" 
                    :class="visible && 'reveal-active'" 
                    style="transition-delay: {{ ($index + 1) * 0.1 }}s">
                    <div class="w-12 h-12 bg-linear-to-br from-primary/10 to-accent/10 rounded-xl flex items-center justify-center mb-6 text-primary group-hover:scale-110 transition-transform duration-300">
                        <x-ui.icon :name="$item['icon'] ?? 'banknotes'" class="w-6 h-6" />
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4 group-hover:text-primary transition-colors">
                        {{ $item['title'] }}
                    </h3>
                    <p class="text-slate-500 dark:text-slate-400 leading-relaxed font-medium">
                        {{ $item['description'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>
