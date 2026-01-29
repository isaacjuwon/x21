@use('App\Settings\LayoutSettings')
@php
    $layoutSettings = app(LayoutSettings::class);
@endphp

<x-layouts::front :header="true">
    <div class="relative bg-slate-50/50 dark:bg-neutral-950">
        
        <!-- Banner Background -->
        @if($layoutSettings->banner)
            <div class="absolute inset-0 -z-20 h-full w-full bg-cover bg-fixed bg-center opacity-5 dark:opacity-10" 
                 style="background-image: url('{{ Storage::url($layoutSettings->banner) }}')"></div>
        @endif
        
        <!-- Abstract Background Glows -->
        <div class="absolute top-[-100px] left-[-100px] w-[500px] h-[500px] bg-primary/10 dark:bg-primary/20 blur-[120px] rounded-full -z-10 animate-slow-ping"></div>
        <div class="absolute top-[20%] right-[-100px] w-[600px] h-[600px] bg-indigo-500/10 dark:bg-indigo-500/15 blur-[100px] rounded-full -z-10"></div>
        <div class="absolute bottom-[20%] left-[-150px] w-[700px] h-[700px] bg-amber-500/5 dark:bg-amber-500/10 blur-[150px] rounded-full -z-10 animate-pulse"></div>
        <div class="absolute bottom-[-100px] right-[-100px] w-[500px] h-[500px] bg-secondary/10 dark:bg-secondary/20 blur-[120px] rounded-full -z-10"></div>

        <!-- Hero Section -->
        <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 px-6 overflow-hidden">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full bg-linear-to-b from-primary/[0.05] via-transparent to-transparent -z-10"></div>
            <div class="max-w-5xl mx-auto text-center relative">
                @if($layoutSettings->banner)
                    <div class="mb-12 relative group reveal-active" style="transition-delay: 0.1s">
                        <div class="absolute -inset-1 bg-linear-to-r from-primary/20 to-indigo-500/20 rounded-[2rem] blur-xl opacity-25 group-hover:opacity-50 transition duration-1000"></div>
                        <div class="relative overflow-hidden rounded-[2rem] border border-neutral-200/50 dark:border-neutral-800/50 shadow-2xl shadow-primary/5">
                            <img src="{{ Str::startsWith($layoutSettings->banner, ['http://', 'https://']) ? $layoutSettings->banner : Storage::url($layoutSettings->banner) }}" 
                                 alt="Banner" 
                                 class="w-full h-auto object-cover max-h-[400px] hover:scale-105 transition-transform duration-700">
                        </div>
                    </div>
                @endif

                <div class="absolute -top-24 -left-24 w-64 h-64 bg-primary/20 blur-3xl rounded-full opacity-50 -z-10 animate-pulse"></div>
                <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-secondary/20 blur-3xl rounded-full opacity-50 -z-10 animate-pulse" style="animation-delay: 1s"></div>
                
                <h1 class="text-5xl lg:text-7xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-primary via-indigo-600 to-secondary mb-8 reveal-active" 
                    style="transition-delay: 0.2s">
                    {{ $layoutSettings->homepage_title ?? 'Financial Freedom for Everyone' }}
                </h1>
                <p class="text-lg lg:text-xl text-slate-600 dark:text-neutral-400 max-w-2xl mx-auto leading-relaxed mb-12 reveal-active"
                    style="transition-delay: 0.4s">
                    {{ $layoutSettings->homepage_description ?? 'Manage your loans, shares, and dividends in one place. Secure, transparent, and easy to use cooperative management system.' }}
                </p>
                
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 reveal-active"
                    style="transition-delay: 0.6s">
                    <x-ui.button 
                        tag="a" 
                        href="{{ route('register') }}" 
                        class="px-8 py-4 bg-linear-to-r from-primary to-indigo-600 text-white hover:glow border-none rounded-2xl font-bold shadow-2xl transition-all hover:-translate-y-1"
                    >
                        Start Journey
                        <span class="ml-2 inline-block transition-transform group-hover:translate-x-1">→</span>
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