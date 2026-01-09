@use('App\Settings\LayoutSettings')
<x-layouts::front :header="false">
    <div class="relative min-h-screen  overflow-x-hidden" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">
        
        <!-- Abstract Background Glows -->
        @if($layoutSettings->banner)
            <div class="absolute inset-0 -z-10 h-full w-full bg-cover bg-fixed bg-center opacity-10 dark:opacity-20 transition-opacity duration-1000" 
                 style="background-image: url('{{ Storage::url($layoutSettings->banner) }}')"
                 :class="loaded ? 'opacity-10 dark:opacity-20' : 'opacity-0'"></div>
        @endif
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[600px] bg-primary/20 blur-[120px] rounded-full -z-10 animate-slow-ping"></div>
        <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-secondary/10 blur-[100px] rounded-full -z-10"></div>
        <div class="absolute top-1/2 left-0 w-[300px] h-[300px] bg-accent/10 blur-[80px] rounded-full -z-10"></div>

        <!-- Navbar (Minimal) -->
        <nav class="fixed top-0 w-full z-50 px-6 py-6 transition-all duration-500" :class="loaded ? 'translate-y-0 opacity-100' : '-translate-y-4 opacity-0'">
            <div class="max-w-7xl mx-auto flex justify-between items-center glass rounded-2xl px-6 py-3 border-white/10">
                <x-app-logo class="h-8" />
                <div class="flex items-center gap-4">
                    <x-ui.button variant="ghost" :href="route('login')" class="text-slate-600 dark:text-slate-400">Login</x-ui.button>
                    <x-ui.button variant="primary" :href="route('register')" class="rounded-xl px-6">
                        Get Started
                    </x-ui.button>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 px-6">
            <div class="max-w-5xl mx-auto text-center">
                <h1 class="text-5xl lg:text-7xl font-extrabold tracking-tight text-transparent bg-clip-text dark:text-white bg-linear-to-r from-primary via-purple-500 to-accent mb-8 reveal" 
                    :class="loaded && 'reveal-active'" style="transition-delay: 0.2s">
                    {{ $layoutSettings->homepage_title ?? 'Financial Freedom for Everyone' }}
                </h1>
                <p class="text-lg lg:text-xl text-slate-600 dark:text-slate-400 max-w-2xl mx-auto leading-relaxed mb-12 reveal"
                    :class="loaded && 'reveal-active'" style="transition-delay: 0.4s">
                    {{ $layoutSettings->homepage_description ?? 'Manage your loans, shares, and dividends in one place. Secure, transparent, and easy to use cooperative management system.' }}
                </p>
                
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 reveal"
                    :class="loaded && 'reveal-active'" style="transition-delay: 0.6s">
                    <x-ui.button 
                        tag="a" 
                        href="{{ route('register') }}" 
                        class="px-8 py-4 bg-linear-to-r from-primary to-secondary text-white rounded-2xl font-bold shadow-2xl transition-all hover:glow hover:-translate-y-1 hover:to-primary"
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
        <footer class="py-12 border-t border-slate-200 dark:border-slate-800/50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-8">
                    <x-app-logo class="h-6 opacity-60" />
                    
                    <div class="flex flex-col items-center md:items-start gap-4">
                        @foreach(\App\Models\Page::all() as $page)
                            <a href="{{ route('pages.show', $page) }}" class="text-sm font-medium text-slate-400 hover:text-primary transition-colors">{{ $page->title }}</a>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-6">
                         @if($layoutSettings->facebook)
                            <a href="{{ $layoutSettings->facebook }}" class="text-slate-400 hover:text-primary transition-colors">
                                <x-ui.icon name="ps:facebook-logo" class="h-5 w-5" />
                            </a>
                        @endif
                        @if($layoutSettings->twitter)
                            <a href="{{ $layoutSettings->twitter }}" class="text-slate-400 hover:text-primary transition-colors">
                                <x-ui.icon name="ps:twitter-logo" class="h-5 w-5" />
                            </a>
                        @endif
                         @if($layoutSettings->email)
                            <a href="mailto:{{ $layoutSettings->email }}" class="text-slate-400 hover:text-primary transition-colors">
                                <x-ui.icon name="envelope" class="h-5 w-5" />
                            </a>
                        @endif
                    </div>
                </div>
                <div class="mt-12 text-center">
                    <p class="text-xs text-slate-500 font-medium opacity-50">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</x-layouts::front>