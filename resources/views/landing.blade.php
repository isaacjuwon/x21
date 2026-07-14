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
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($generalSettings->site_logo) }}" alt="{{ $generalSettings->site_name }}" class="h-8 w-auto">
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
            <!-- Decorative background elements -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full -z-10 overflow-hidden pointer-events-none">
                <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-primary-color/5 blur-[120px] rounded-full"></div>
                <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-primary-color/10 blur-[120px] rounded-full"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="text-center">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-color/10 text-primary-color text-xs font-bold tracking-wider uppercase mb-8 animate-fade-in">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-color opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-color"></span>
                        </span>
                        The Future of Fintech
                    </div>
                    <h1 class="text-5xl lg:text-7xl font-extrabold tracking-tight mb-6 leading-[1.1]">
                        <span class="block text-zinc-900 dark:text-white">{{ $layoutSettings->homepage_title }}</span>
                    </h1>
                    <p class="max-w-2xl mx-auto text-xl text-zinc-600 dark:text-zinc-400 mb-10 leading-relaxed">
                        {{ $layoutSettings->homepage_description }}
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <flux:button href="{{ route('register') }}" variant="primary" class="px-8 shadow-lg shadow-primary-color/20">
                            Start for Free
                        </flux:button>
                        <flux:button href="#features" variant="outline" class="px-8">
                            Learn More
                        </flux:button>
                    </div>
                </div>

                <!-- Hero Image/Banner -->
                @if($layoutSettings->banner)
                    <div class="mt-20 rounded-3xl overflow-hidden shadow-2xl border border-zinc-200 dark:border-zinc-800 transform hover:scale-[1.01] transition-transform duration-700">
                        <img src="{{ $layoutSettings->banner }}" alt="Banner" class="w-full object-cover aspect-video">
                    </div>
                @else
                    <div class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-8">
                         <div class="group aspect-video bg-white dark:bg-zinc-900 rounded-2xl flex flex-col items-center justify-center border border-zinc-200 dark:border-zinc-800 shadow-sm hover:shadow-xl hover:border-primary-color/30 transition-all duration-500">
                            <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-xl group-hover:bg-primary-color/10 transition-colors">
                                <flux:icon.wallet class="size-8 text-zinc-400 group-hover:text-primary-color" />
                            </div>
                            <span class="mt-4 font-semibold text-zinc-500 group-hover:text-zinc-900 dark:group-hover:text-zinc-100">Smart Wallet</span>
                         </div>
                         <div class="group aspect-video bg-white dark:bg-zinc-900 rounded-2xl flex flex-col items-center justify-center border border-zinc-200 dark:border-zinc-800 shadow-sm hover:shadow-xl hover:border-primary-color/30 transition-all duration-500">
                            <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-xl group-hover:bg-primary-color/10 transition-colors">
                                <flux:icon.banknotes class="size-8 text-zinc-400 group-hover:text-primary-color" />
                            </div>
                            <span class="mt-4 font-semibold text-zinc-500 group-hover:text-zinc-900 dark:group-hover:text-zinc-100">Quick Loans</span>
                         </div>
                         <div class="group aspect-video bg-white dark:bg-zinc-900 rounded-2xl flex flex-col items-center justify-center border border-zinc-200 dark:border-zinc-800 shadow-sm hover:shadow-xl hover:border-primary-color/30 transition-all duration-500">
                            <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-xl group-hover:bg-primary-color/10 transition-colors">
                                <flux:icon.chart-bar class="size-8 text-zinc-400 group-hover:text-primary-color" />
                            </div>
                            <span class="mt-4 font-semibold text-zinc-500 group-hover:text-zinc-900 dark:group-hover:text-zinc-100">Share Trading</span>
                         </div>
                    </div>
                @endif
            </div>
        </section>

        <!-- How it Works Section -->
        <section class="py-24 border-y border-zinc-100 dark:border-zinc-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold mb-4">How it Works</h2>
                    <p class="text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto">Get started in three simple steps and take control of your financial future.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-12 relative">
                    <!-- Connector line for desktop -->
                    <div class="hidden md:block absolute top-12 left-0 w-full h-px bg-zinc-100 dark:bg-zinc-800 -z-10"></div>
                    
                    <div class="text-center space-y-6">
                        <div class="size-16 bg-white dark:bg-zinc-950 rounded-2xl shadow-md border border-zinc-100 dark:border-zinc-800 flex items-center justify-center mx-auto relative">
                            <span class="absolute -top-2 -right-2 size-6 bg-primary-color text-white text-xs font-bold rounded-full flex items-center justify-center">1</span>
                            <flux:icon.identification class="size-8 text-primary-color" />
                        </div>
                        <h3 class="text-xl font-bold">Create Account</h3>
                        <p class="text-zinc-600 dark:text-zinc-400">Sign up in minutes with your basic details and complete a quick KYC verification.</p>
                    </div>

                    <div class="text-center space-y-6">
                        <div class="size-16 bg-white dark:bg-zinc-950 rounded-2xl shadow-md border border-zinc-100 dark:border-zinc-800 flex items-center justify-center mx-auto relative">
                            <span class="absolute -top-2 -right-2 size-6 bg-primary-color text-white text-xs font-bold rounded-full flex items-center justify-center">2</span>
                            <flux:icon.wallet class="size-8 text-primary-color" />
                        </div>
                        <h3 class="text-xl font-bold">Fund Wallet</h3>
                        <p class="text-zinc-600 dark:text-zinc-400">Deposit funds securely into your smart wallet using multiple payment methods.</p>
                    </div>

                    <div class="text-center space-y-6">
                        <div class="size-16 bg-white dark:bg-zinc-950 rounded-2xl shadow-md border border-zinc-100 dark:border-zinc-800 flex items-center justify-center mx-auto relative">
                            <span class="absolute -top-2 -right-2 size-6 bg-primary-color text-white text-xs font-bold rounded-full flex items-center justify-center">3</span>
                            <flux:icon.bolt class="size-8 text-primary-color" />
                        </div>
                        <h3 class="text-xl font-bold">Start Using Services</h3>
                        <p class="text-zinc-600 dark:text-zinc-400">Apply for loans, buy shares, or pay bills instantly from your dashboard.</p>
                    </div>
                </div>
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
        @php
            $visiblePages = \App\Models\Page::where('is_visible', true)->get();
        @endphp
        <footer id="contact" class="bg-zinc-50 dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-800 pt-16 pb-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
                    <div class="col-span-1 md:col-span-1">
                        <div class="flex items-center gap-2 mb-6">
                            @if($generalSettings->site_logo)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($generalSettings->site_logo) }}" alt="{{ $generalSettings->site_name }}" class="h-6 w-auto">
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
                            @foreach($visiblePages as $page)
                                <li><a href="{{ route('page.show', $page->slug) }}" class="hover:text-primary-color transition-colors">{{ $page->title }}</a></li>
                            @endforeach
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
