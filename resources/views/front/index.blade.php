@use('App\Settings\LayoutSettings')
<x-layouts::front :header="true">
    <div class="relative min-h-screen  overflow-x-hidden" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">
        
        <!-- Abstract Background Glows -->
        @if($layoutSettings->banner)
            <div class="absolute inset-0 -z-10 h-full w-full bg-cover bg-fixed bg-center transition-opacity duration-1000" 
                 style="background-image: url('{{ Storage::url($layoutSettings->banner) }}')"
                 :class="loaded ? 'opacity-10 dark:opacity-20' : 'opacity-0'"></div>
        @endif
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[600px] bg-primary/20 blur-[120px] rounded-full -z-10 animate-slow-ping"></div>
        <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-secondary/10 blur-[100px] rounded-full -z-10"></div>
        <div class="absolute top-1/2 left-0 w-[300px] h-[300px] bg-accent/10 blur-[80px] rounded-full -z-10"></div>

        <!-- Hero Section -->
        <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 px-6">
            <div class="max-w-5xl mx-auto text-center">
                <h1 class="text-5xl lg:text-7xl font-extrabold tracking-tight text-transparent bg-clip-text bg-linear-to-r from-primary via-accent to-secondary mb-8 reveal" 
                    :class="loaded && 'reveal-active'" style="transition-delay: 0.2s">
                    {{ $layoutSettings->homepage_title ?? 'Financial Freedom for Everyone' }}
                </h1>
                <p class="text-lg lg:text-xl text-foreground-content max-w-2xl mx-auto leading-relaxed mb-12 reveal"
                    :class="loaded && 'reveal-active'" style="transition-delay: 0.4s">
                    {{ $layoutSettings->homepage_description ?? 'Manage your loans, shares, and dividends in one place. Secure, transparent, and easy to use cooperative management system.' }}
                </p>
                
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 reveal"
                    :class="loaded && 'reveal-active'" style="transition-delay: 0.6s">
                    <x-ui.button 
                        tag="a" 
                        href="{{ route('register') }}" 
                        class="px-8 py-4 bg-linear-to-r from-primary to-secondary text-primary-fg rounded-2xl font-bold shadow-2xl transition-all hover:glow hover:-translate-y-1 hover:to-primary"
                    >
                        Start Journey
                        <span class="ml-2 transition-transform group-hover:translate-x-1 inline-block">→</span>
                    </x-ui.button>
                </div>
            </div>
        </section>

        <!-- Features Dynamic -->
        <x-landing.features />

        <!-- FAQ Section Dynamic -->
        <x-landing.faq />

        <!-- Footer (Clean) -->
        <x-layouts.front.footer />
    </div>
</x-layouts::front>