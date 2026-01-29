<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
     
    </head>
    
    <body class="min-h-screen bg-background text-foreground">
       <x-ui.layout>
            <x-ui.sidebar>
                <x-slot:brand>
                    {{-- Logo moved to navbar --}}
                </x-slot:brand>
                <x-ui.navlist>
                    <x-ui.navlist.group label="Main">
                        <x-ui.navlist.item 
                            label="Dashboard"
                            icon="home"
                            :href="route('dashboard')"
                            :active="request()->is('dashboard')"
                        />
                    </x-ui.navlist.group>

                    <x-ui.navlist.group 
                        label="Trade"
                        collapsable
                    >
                            <x-ui.navlist.item 
                                label="Airtime"
                                icon="device-phone-mobile"
                                :href="route('airtime')"
                            />
                            <x-ui.navlist.item 
                                label="Internet Data"
                                icon="wifi"
                                :href="route('data')"
                            />
                            <x-ui.navlist.item 
                                label="Electricity Bill"
                                icon="light-bulb"
                                :href="route('electricity')"
                            />
                            <x-ui.navlist.item 
                                label="Cable TV"
                                icon="tv"
                                :href="route('cable')"
                            />
                            <x-ui.navlist.item 
                                label="Education (Pins)"
                                icon="academic-cap"
                                :href="route('education')"
                            />
                            
                    </x-ui.navlist.group>
                    

                    <x-ui.navlist.group 
                        label="Management"
                        collapsable
                    >
                        <x-ui.navlist.item 
                            label="Shares"
                            icon="currency-dollar"
                            :href="route('shares.index')"
                        />

                         <x-ui.navlist.item 
                            label="Loans"
                            icon="banknotes"
                            :href="route('loan.index')"
                        />
                        
                        <x-ui.navlist.item 
                            label="Wallet"
                            icon="credit-card"
                            :href="route('wallet.index')"
                        />

                        <x-ui.navlist.item 
                            label="KYC Verification"
                            icon="shield-check"
                            :href="route('kyc.index')"
                            :active="request()->routeIs('kyc.*')"
                        />
                    </x-ui.navlist.group>
                    
                    <x-ui.navlist.group 
                        label="Support"
                        collapsable
                    >
                        <x-ui.navlist.item 
                            label="Tickets"
                            icon="ticket"
                            :href="route('tickets.index')"
                            :active="request()->routeIs('tickets.*')"
                        />
                    </x-ui.navlist.group>
                    
                    <x-ui.navlist.group 
                        label="Tools and Settings"
                        collapsable
                    >
                        <x-ui.navlist.item 
                            label="Analytics"
                            icon="chart-bar"
                            :href="route('analytics.index')"
                            :active="request()->routeIs('analytics.*')"
                        />
                        
                        @role('admin')
                            <x-ui.navlist.item 
                                label="Admin Dashboard"
                                icon="cog-6-tooth"
                                :href="route('admin.dashboard')"
                            />
                            
                            <x-ui.navlist.item 
                                label="Referrals"
                                icon="user-group"
                                :href="route('admin.referrals.index')"
                                :active="request()->routeIs('admin.referrals.*')"
                            />
                        @endrole
                        
                        <x-ui.navlist.item 
                            label="Settings"
                            icon="cog"
                            :href="route('profile.edit')"
                        />
                    </x-ui.navlist.group>
                
                    
                </x-ui.navlist>

                <x-ui.sidebar.push />

                <x-ui.dropdown portal>
                    <x-slot:button>
                        <div class="flex items-center gap-3 px-3 py-2 rounded-[--radius-field] hover:bg-neutral-100 dark:hover:bg-neutral-800/50 transition-colors">
                            <x-ui.avatar size="sm" :src="auth()->user()->avatar_url" circle alt="Profile Picture" class="ring-2 ring-neutral-100 dark:ring-neutral-700" />
                            <div class="flex flex-col items-start min-w-0">
                                <span class="text-xs font-bold text-neutral-900 dark:text-white truncate max-w-[150px]">
                                    {{ auth()->user()->name }}
                                </span>
                                <span class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 truncate max-w-[150px] uppercase tracking-widest">
                                    {{ auth()->user()->email }}
                                </span>
                            </div>
                        </div>
                    </x-slot:button>
                    
                    <x-slot:menu class="!w-[14rem] p-1 bg-white dark:bg-neutral-900 border-neutral-100 dark:border-neutral-700 rounded-[--radius-box] shadow-xl">
                        <x-ui.dropdown.item :href="route('profile.edit')" icon="adjustments-horizontal" class="text-xs font-bold uppercase tracking-widest text-neutral-600 dark:text-neutral-300">
                            Preference
                        </x-ui.dropdown.item>
                        
                        <x-ui.dropdown.item :href="route('profile.edit')" icon="user-circle" class="text-xs font-bold uppercase tracking-widest text-neutral-600 dark:text-neutral-300">
                            Profile
                        </x-ui.dropdown.item>
                        
                        <x-ui.dropdown.item :href="route('profile.edit')" icon="lock-closed" class="text-xs font-bold uppercase tracking-widest text-neutral-600 dark:text-neutral-300">
                            Security
                        </x-ui.dropdown.item>
                        
                        <x-ui.dropdown.item href="#" icon="bell" variant="danger" class="text-xs font-bold uppercase tracking-widest">
                            Notifications
                        </x-ui.dropdown.item>
                    </x-slot:menu>
                </x-ui.dropdown>
            </x-ui.sidebar>
            <x-ui.layout.main>
                <x-ui.layout.header>
                    <x-ui.sidebar.toggle class="md:hidden"/>
                    <div class="flex items-center gap-2">
                        <x-app-logo class="h-6 ml-2" />
                    </div>
                    <x-ui.navbar class="flex-1 hidden lg:flex">
                        <x-ui.navbar.item
                            icon="home"
                            label="Home" 
                            :href="route('dashboard')"
                        />
                        <x-ui.navbar.item 
                            icon="cog" 
                            label="Settings" 
                            badge:color="orange"
                            badge:variant="outline"
                            :href="route('profile.edit')"
                        />
                       
                    </x-ui.navbar>

                    <div class="flex ml-auto gap-x-3 items-center">
                            <x-ui.dropdown position="bottom-end">
                            <x-slot:button class="justify-center">
                                <x-ui.avatar size="sm" :src="auth()->user()->avatar_url" circle alt="Profile Picture" />
                            </x-slot:button>

                            <x-slot:menu class="w-64 p-1 bg-white dark:bg-neutral-900 border-neutral-100 dark:border-neutral-700 rounded-[--radius-box] shadow-xl">
                                <x-ui.dropdown.group label="signed in as" class="text-[10px] font-bold uppercase tracking-widest text-neutral-400">
                                    <x-ui.dropdown.item class="text-xs font-bold text-neutral-900 dark:text-white truncate">
                                        {{ Auth::user()->email }}
                                    </x-ui.dropdown.item>
                                </x-ui.dropdown.group>

                                <x-ui.dropdown.separator class="bg-neutral-100 dark:bg-neutral-800" />

                                <x-ui.dropdown.item :href="route('profile.edit')" wire:navigate class="text-xs font-bold uppercase tracking-widest text-neutral-600 dark:text-neutral-300">
                                    Account Settings
                                </x-ui.dropdown.item>

                                <x-ui.dropdown.separator class="bg-neutral-100 dark:bg-neutral-800" />

                                <form
                                    action="{{ route('logout') }}"
                                    method="post"
                                    class="contents"
                                >
                                    @csrf
                                    <x-ui.dropdown.item as="button" type="submit" class="w-full text-xs font-bold uppercase tracking-widest text-error">
                                        Sign Out
                                    </x-ui.dropdown.item>
                                </form>

                            </x-slot:menu>
                        </x-ui.dropdown>

                        <x-ui.theme-switcher variant="inline" />
                    </div>
                </x-ui.layout.header>
                <!-- Your page content -->
                <div class="p-6">
                    {{ $slot }}
                </div>
            </x-ui.layout.main>
        </x-ui.layout>
    @vite(['resources/js/app.js'])
    
    <!-- Ensure dark mode is applied after scripts load, this is also required to prevent flickering when many livewire component changes indepently -->
    <script>
        loadDarkMode()
    </script>
        {{-- without this it cause flicker when multiple components changes in isolation in the  page --}}
       
    </body>
</html>