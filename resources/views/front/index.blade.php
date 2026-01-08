@use('App\Settings\LayoutSettings')
<x-layouts::front>
    <div class="bg-white dark:bg-gray-900">
        <!-- Hero Section -->
        <div class="relative isolate px-6 pt-14 lg:px-8 overflow-hidden">
            @if($layoutSettings->banner)
                <div class="absolute inset-0 -z-10 h-full w-full bg-cover bg-center opacity-20" style="background-image: url('{{ $layoutSettings->banner }}')"></div>
            @else
                <div class="absolute inset-0 -z-10 h-full w-full bg-white dark:bg-gray-950 [background:radial-gradient(125%_125%_at_50%_10%,#fff_40%,#63e_100%)] dark:[background:radial-gradient(125%_125%_at_50%_10%,#000_40%,#63e_100%)] opacity-20"></div>
            @endif
            
            <div class="mx-auto max-w-2xl py-32 sm:py-48 lg:py-56">
                <div class="text-center">
                    <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-6xl">
                        {{ $layoutSettings->homepage_title ?? 'Financial Freedom for Everyone' }}
                    </h1>
                    <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-400">
                        {{ $layoutSettings->homepage_description ?? 'Manage your loans, shares, and dividends in one place. Secure, transparent, and easy to use cooperative management system.' }}
                    </p>
                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        <a href="{{ route('register') }}" class="rounded-md bg-primary text-primary-fg px-3.5 py-2.5 text-sm font-semibold shadow-sm hover:opacity-90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">Get started</a>
                        <a href="{{ route('login') }}" class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">Log in <span aria-hidden="true">→</span></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feature Section -->
        <x-landing.features />

        <!-- FAQ Section -->
        <x-landing.faq />

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800" aria-labelledby="footer-heading">
            <h2 id="footer-heading" class="sr-only">Footer</h2>
            <div class="mx-auto max-w-7xl px-6 pb-8 pt-16 sm:pt-24 lg:px-8 lg:pt-32">
                <div class="xl:grid xl:grid-cols-3 xl:gap-8">
                    <div class="space-y-8">
                        <x-app-logo class="h-7" />
                        @if($layoutSettings->about)
                            <p class="text-sm leading-6 text-gray-600 dark:text-gray-400">
                                {{ $layoutSettings->about }}
                            </p>
                        @endif
                        <div class="flex space-x-6">
                            @if($layoutSettings->facebook)
                                <a href="{{ $layoutSettings->facebook }}" class="text-gray-400 hover:text-gray-500">
                                    <span class="sr-only">Facebook</span>
                                    <x-ui.icon name="ps:facebook-logo" class="h-6 w-6" />
                                </a>
                            @endif
                            @if($layoutSettings->instagram)
                                <a href="{{ $layoutSettings->instagram }}" class="text-gray-400 hover:text-gray-500">
                                    <span class="sr-only">Instagram</span>
                                    <x-ui.icon name="ps:instagram-logo" class="h-6 w-6" />
                                </a>
                            @endif
                            @if($layoutSettings->twitter)
                                <a href="{{ $layoutSettings->twitter }}" class="text-gray-400 hover:text-gray-500">
                                    <span class="sr-only">Twitter</span>
                                    <x-ui.icon name="ps:twitter-logo" class="h-6 w-6" />
                                </a>
                            @endif
                             @if($layoutSettings->email)
                                <a href="mailto:{{ $layoutSettings->email }}" class="text-gray-400 hover:text-gray-500">
                                    <span class="sr-only">Email</span>
                                    <x-ui.icon name="heroicon-o-envelope" class="h-6 w-6" />
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="mt-16 grid grid-cols-2 gap-8 xl:col-span-2 xl:mt-0">
                        <div class="md:grid md:grid-cols-2 md:gap-8">
                            <div>
                                <h3 class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">Legal</h3>
                                <ul role="list" class="mt-6 space-y-4">
                                    @foreach(\App\Models\Page::all() as $page)
                                        <li>
                                            <a href="{{ route('pages.show', $page) }}" class="text-sm leading-6 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">{{ $page->title }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="mt-10 md:mt-0">
                                @if($layoutSettings->address)
                                    <h3 class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">Contact Us</h3>
                                    <p class="mt-6 text-sm leading-6 text-gray-600 dark:text-gray-400 whitespace-pre-line">
                                        {{ $layoutSettings->address }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-16 border-t border-gray-900/10 dark:border-gray-100/10 pt-8 sm:mt-20 lg:mt-24">
                    <p class="text-xs leading-5 text-gray-500">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</x-layouts::front>