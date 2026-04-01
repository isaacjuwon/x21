<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        @include('partials.head')
    </head>
    <body class="bg-white dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 antialiased">
        <!-- Navbar -->
        <nav class="sticky top-0 z-50 bg-white/80 dark:bg-zinc-950/80 backdrop-blur-md border-b border-zinc-200 dark:border-zinc-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-2">
                        @if($generalSettings->site_logo)
                            <img src="{{ $generalSettings->site_logo }}" alt="{{ $generalSettings->site_name }}" class="h-8 w-auto">
                        @else
                            <x-app-logo class="h-8 w-auto text-primary-color" />
                        @endif
                        <span class="text-xl font-bold tracking-tight">{{ $generalSettings->site_name }}</span>
                    </div>

                    <div class="hidden md:flex items-center gap-8 text-sm font-medium">
                        <a href="#features" class="hover:text-primary-color transition-colors">Features</a>
                        <a href="#about" class="hover:text-primary-color transition-colors">About</a>
                        <a href="#faq" class="hover:text-primary-color transition-colors">FAQ</a>
                        <a href="#contact" class="hover:text-primary-color transition-colors">Contact</a>
                    </div>

                    <div class="flex items-center gap-4">
                        @auth
                            <flux:button href="{{ route('dashboard') }}" variant="primary" size="sm" wire:navigate>
                                Dashboard
                            </flux:button>
                        @else
                            <flux:button href="{{ route('login') }}" variant="ghost" size="sm" wire:navigate>
                                Log in
                            </flux:button>
                            @if (Route::has('register'))
                                <flux:button href="{{ route('register') }}" variant="primary" size="sm" wire:navigate>
                                    Get Started
                                </flux:button>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative py-20 lg:py-32 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="text-center">
                    <h1 class="text-5xl lg:text-7xl font-extrabold tracking-tight mb-6">
                        <span class="block">{{ $layoutSettings->homepage_title }}</span>
                    </h1>
                    <p class="max-w-2xl mx-auto text-xl text-zinc-600 dark:text-zinc-400 mb-10">
                        {{ $layoutSettings->homepage_description }}
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <flux:button href="{{ route('register') }}" variant="primary" size="sm" class="px-8">
                            Start for Free
                        </flux:button>
                        <flux:button href="#features" variant="outline" size="sm" class="px-8">
                            Learn More
                        </flux:button>
                    </div>
                </div>

                <!-- Hero Image/Banner -->
                @if($layoutSettings->banner)
                    <div class="mt-16 rounded-2xl overflow-hidden shadow-2xl border border-zinc-200 dark:border-zinc-800">
                        <img src="{{ $layoutSettings->banner }}" alt="Banner" class="w-full object-cover aspect-video">
                    </div>
                @else
                    <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8 opacity-50 grayscale hover:grayscale-0 transition-all duration-700">
                         <div class="aspect-video bg-zinc-100 dark:bg-zinc-900 rounded-xl flex items-center justify-center border border-dashed border-zinc-300 dark:border-zinc-700">
                            <flux:icon.wallet class="size-12 text-zinc-400" />
                         </div>
                         <div class="aspect-video bg-zinc-100 dark:bg-zinc-900 rounded-xl flex items-center justify-center border border-dashed border-zinc-300 dark:border-zinc-700">
                            <flux:icon.banknotes class="size-12 text-zinc-400" />
                         </div>
                         <div class="aspect-video bg-zinc-100 dark:bg-zinc-900 rounded-xl flex items-center justify-center border border-dashed border-zinc-300 dark:border-zinc-700">
                            <flux:icon.chart-bar class="size-12 text-zinc-400" />
                         </div>
                    </div>
                @endif
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 bg-zinc-50 dark:bg-zinc-900/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold mb-4">{{ $layoutSettings->homepage_features_title }}</h2>
                    <p class="text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto">
                        {{ $layoutSettings->homepage_features_description }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($layoutSettings->homepage_features_items as $feature)
                        <flux:card class="p-8 flex flex-col items-start gap-4 hover:shadow-lg transition-shadow border-zinc-200 dark:border-zinc-800">
                            <div class="p-3 bg-primary-color/10 rounded-xl">
                                @if(isset($feature['icon']))
                                    <flux:icon :name="$feature['icon']" class="size-6 text-primary-color" />
                                @else
                                    <flux:icon.layout-grid class="size-6 text-primary-color" />
                                @endif
                            </div>
                            <div>
                                <h3 class="text-xl font-bold mb-2">{{ $feature['title'] }}</h3>
                                <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed">
                                    {{ $feature['description'] }}
                                </p>
                            </div>
                        </flux:card>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <div>
                        <h2 class="text-4xl font-bold mb-6">Built for Modern Finance</h2>
                        <p class="text-lg text-zinc-600 dark:text-zinc-400 mb-8">
                            {{ $layoutSettings->about ?? $generalSettings->site_description }}
                        </p>
                        <ul class="space-y-4">
                            <li class="flex items-center gap-3">
                                <flux:icon.check-circle class="size-5 text-green-500" />
                                <span>Secure Wallet Management</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <flux:icon.check-circle class="size-5 text-green-500" />
                                <span>Fast & Flexible Loan Systems</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <flux:icon.check-circle class="size-5 text-green-500" />
                                <span>Transparent Share Trading</span>
                            </li>
                        </ul>
                    </div>
                    <div class="bg-zinc-100 dark:bg-zinc-900 rounded-3xl p-8 border border-zinc-200 dark:border-zinc-800">
                         <div class="grid grid-cols-2 gap-4">
                            <div class="p-6 bg-white dark:bg-zinc-950 rounded-2xl shadow-sm border border-zinc-100 dark:border-zinc-800">
                                <div class="text-3xl font-bold text-primary-color mb-1">99.9%</div>
                                <div class="text-sm text-zinc-500">Uptime</div>
                            </div>
                            <div class="p-6 bg-white dark:bg-zinc-950 rounded-2xl shadow-sm border border-zinc-100 dark:border-zinc-800">
                                <div class="text-3xl font-bold text-primary-color mb-1">24/7</div>
                                <div class="text-sm text-zinc-500">Support</div>
                            </div>
                            <div class="p-6 bg-white dark:bg-zinc-950 rounded-2xl shadow-sm border border-zinc-100 dark:border-zinc-800">
                                <div class="text-3xl font-bold text-primary-color mb-1">Secure</div>
                                <div class="text-sm text-zinc-500">Encrypted</div>
                            </div>
                            <div class="p-6 bg-white dark:bg-zinc-950 rounded-2xl shadow-sm border border-zinc-100 dark:border-zinc-800">
                                <div class="text-3xl font-bold text-primary-color mb-1">Global</div>
                                <div class="text-sm text-zinc-500">Reach</div>
                            </div>
                         </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        @if($faqs->isNotEmpty())
        <section id="faq" class="py-20">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold mb-4">Frequently Asked Questions</h2>
                    <p class="text-zinc-600 dark:text-zinc-400">Everything you need to know. Can't find the answer? <a href="{{ route('login') }}" class="text-primary-color hover:underline">Open a support ticket</a>.</p>
                </div>

                <div class="divide-y divide-zinc-200 dark:divide-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-2xl overflow-hidden">
                    @foreach($faqs as $faq)
                        <details class="group bg-white dark:bg-zinc-900">
                            <summary class="flex items-center justify-between gap-4 px-6 py-5 cursor-pointer list-none font-medium hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <span>{{ $faq->question }}</span>
                                <flux:icon.chevron-down class="size-5 text-zinc-400 shrink-0 transition-transform group-open:rotate-180" />
                            </summary>
                            <div class="px-6 pb-5 text-zinc-600 dark:text-zinc-400 leading-relaxed">
                                {{ $faq->answer }}
                            </div>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        <!-- Contact/Footer Section -->
        <footer id="contact" class="bg-zinc-50 dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-800 pt-16 pb-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
                    <div class="col-span-1 md:col-span-1">
                        <div class="flex items-center gap-2 mb-6">
                            @if($generalSettings->site_logo)
                                <img src="{{ $generalSettings->site_logo }}" alt="{{ $generalSettings->site_name }}" class="h-6 w-auto">
                            @else
                                <x-app-logo class="h-6 w-auto text-primary-color" />
                            @endif
                            <span class="text-lg font-bold tracking-tight">{{ $generalSettings->site_name }}</span>
                        </div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed mb-6">
                            {{ $generalSettings->site_description }}
                        </p>
                        <div class="flex gap-4">
                            @if($layoutSettings->facebook)
                                <a href="{{ $layoutSettings->facebook }}" class="text-zinc-400 hover:text-primary-color transition-colors">
                                    <flux:icon.facebook class="size-5" />
                                </a>
                            @endif
                            @if($layoutSettings->twitter)
                                <a href="{{ $layoutSettings->twitter }}" class="text-zinc-400 hover:text-primary-color transition-colors">
                                    <flux:icon.twitter class="size-5" />
                                </a>
                            @endif
                            @if($layoutSettings->instagram)
                                <a href="{{ $layoutSettings->instagram }}" class="text-zinc-400 hover:text-primary-color transition-colors">
                                    <flux:icon.instagram class="size-5" />
                                </a>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h4 class="font-bold mb-6 uppercase text-xs tracking-widest text-zinc-400">Services</h4>
                        <ul class="space-y-4 text-sm font-medium">
                            <li><a href="#" class="hover:text-primary-color transition-colors">Wallet</a></li>
                            <li><a href="#" class="hover:text-primary-color transition-colors">Loans</a></li>
                            <li><a href="#" class="hover:text-primary-color transition-colors">Shares</a></li>
                            <li><a href="#" class="hover:text-primary-color transition-colors">Virtual Topup</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-bold mb-6 uppercase text-xs tracking-widest text-zinc-400">Company</h4>
                        <ul class="space-y-4 text-sm font-medium">
                            <li><a href="#" class="hover:text-primary-color transition-colors">About Us</a></li>
                            <li><a href="#" class="hover:text-primary-color transition-colors">Privacy Policy</a></li>
                            <li><a href="#" class="hover:text-primary-color transition-colors">Terms of Service</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-bold mb-6 uppercase text-xs tracking-widest text-zinc-400">Contact</h4>
                        <ul class="space-y-4 text-sm font-medium">
                            @if($layoutSettings->address)
                                <li class="flex items-start gap-3">
                                    <flux:icon.map-pin class="size-5 text-zinc-400 shrink-0" />
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ $layoutSettings->address }}</span>
                                </li>
                            @endif
                            @if($layoutSettings->email ?? $generalSettings->contact_email)
                                <li class="flex items-center gap-3">
                                    <flux:icon.mail class="size-5 text-zinc-400 shrink-0" />
                                    <a href="mailto:{{ $layoutSettings->email ?? $generalSettings->contact_email }}" class="hover:text-primary-color transition-colors text-zinc-600 dark:text-zinc-400">
                                        {{ $layoutSettings->email ?? $generalSettings->contact_email }}
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <div class="pt-8 border-t border-zinc-200 dark:border-zinc-800 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-zinc-500">
                    <p>&copy; {{ date('Y') }} {{ $generalSettings->site_name }}. All rights reserved.</p>
                    <div class="flex gap-6">
                        <a href="#" class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">Privacy</a>
                        <a href="#" class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">Terms</a>
                        <a href="#" class="hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors">Cookies</a>
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>
