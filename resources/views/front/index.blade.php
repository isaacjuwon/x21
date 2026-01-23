@use('App\Settings\LayoutSettings')
@php
    $layoutSettings = app(LayoutSettings::class);
@endphp

<x-layouts::front :header="true">
    <div class="relative" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">
        
        <!-- Abstract Background Glows -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[600px] bg-primary/5 dark:bg-primary/10 blur-[120px] rounded-full -z-10 animate-slow-ping"></div>
        <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-secondary/5 dark:bg-secondary/10 blur-[100px] rounded-full -z-10"></div>

        <!-- Hero Section -->
        <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 px-6">
            <div class="max-w-5xl mx-auto text-center">
                <h1 class="text-5xl lg:text-7xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-primary via-indigo-500 to-secondary mb-8 reveal" 
                    :class="loaded && 'reveal-active'" style="transition-delay: 0.2s">
                    {{ $layoutSettings->homepage_title ?? 'Financial Freedom for Everyone' }}
                </h1>
                <p class="text-lg lg:text-xl text-slate-600 dark:text-neutral-400 max-w-2xl mx-auto leading-relaxed mb-12 reveal"
                    :class="loaded && 'reveal-active'" style="transition-delay: 0.4s">
                    {{ $layoutSettings->homepage_description ?? 'Manage your loans, shares, and dividends in one place. Secure, transparent, and easy to use cooperative management system.' }}
                </p>
                
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 reveal"
                    :class="loaded && 'reveal-active'" style="transition-delay: 0.6s">
                    <x-ui.button 
                        tag="a" 
                        href="{{ route('register') }}" 
                        class="px-8 py-4 bg-primary text-white hover:bg-primary/90 border-none rounded-2xl font-bold shadow-xl transition-all hover:-translate-y-1"
                    >
                        Start Journey
                        <span class="ml-2 inline-block">→</span>
                    </x-ui.button>
                </div>
            </div>
        </section>

        <!-- Features Dynamic -->
        <x-landing.features />

        <!-- FAQ Section Dynamic -->
        <x-landing.faq />

        <!-- Footer -->
        <x-layouts.front.footer />
    </div>
</x-layouts::front>