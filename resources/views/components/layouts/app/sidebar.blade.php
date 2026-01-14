<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
     
    </head>
     @livewireStyles
    
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
                        <div class="flex items-center gap-3 px-2 py-1.5 rounded-lg hover:bg-background-content transition-colors">
                            <x-ui.avatar size="sm" :src="auth()->user()->avatar_url" circle alt="Profile Picture" />
                            <div class="flex flex-col items-start min-w-0">
                                <span class="text-sm font-medium text-foreground truncate max-w-[150px]">
                                    {{ auth()->user()->name }}
                                </span>
                                <span class="text-xs text-foreground-content truncate max-w-[150px]">
                                    {{ auth()->user()->email }}
                                </span>
                            </div>
                        </div>
                    </x-slot:button>
                    
                    <x-slot:menu class="!w-[14rem]">
                        <x-ui.dropdown.item :href="route('profile.edit')" icon="adjustments-horizontal">
                            Preference
                        </x-ui.dropdown.item>
                        
                        <x-ui.dropdown.item :href="route('profile.edit')" icon="user-circle">
                            Profile
                        </x-ui.dropdown.item>
                        
                        <x-ui.dropdown.item :href="route('profile.edit')" icon="lock-closed">
                            Security
                        </x-ui.dropdown.item>
                        
                        <x-ui.dropdown.item href="#" icon="bell" variant="danger">
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

                            <x-slot:menu class="w-56">
                                <x-ui.dropdown.group label="signed in as">
                                    <x-ui.dropdown.item>
                                        {{ Auth::user()->email }}
                                    </x-ui.dropdown.item>
                                </x-ui.dropdown.group>

                                <x-ui.dropdown.separator />

                                <x-ui.dropdown.item :href="route('profile.edit')" wire:navigate>
                                    Account
                                </x-ui.dropdown.item>

                                <x-ui.dropdown.separator />

                                <form
                                    action="{{ route('logout') }}"
                                    method="post"
                                    class="contents"
                                >
                                    @csrf
                                    <x-ui.dropdown.item as="button" type="submit">
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
         @livewireScripts
        {{-- without this it cause flicker when multiple components changes in isolation in the  page --}}
       
    </body>
</html>