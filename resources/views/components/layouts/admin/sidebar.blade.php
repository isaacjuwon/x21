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
                            :href="route('admin.dashboard')"
                            :active="request()->is('admin.dashboard')"
                        />
                        <x-ui.navlist.item 
                            label="Analytics"
                            icon="chart-bar"
                            :href="route('admin.analytics.index')"
                            :active="request()->routeIs('admin.analytics.*')"
                        />
                    </x-ui.navlist.group>

                    <x-ui.navlist.group 
                        label="Trade"
                        collapsable
                    >
                            <x-ui.navlist.item 
                                label="Airtime"
                                icon="device-phone-mobile"
                                :href="route('admin.airtime.index')"
                            />
                            <x-ui.navlist.item 
                                label="Internet Data"
                                icon="wifi"
                                :href="route('admin.data.index')"
                            />
                            <x-ui.navlist.item 
                                label="Electricity Bill"
                                icon="light-bulb"
                                :href="route('admin.electricity.index')"
                            />
                            <x-ui.navlist.item 
                                label="Cable TV"
                                icon="tv"
                                :href="route('admin.cable.index')"
                            />
                            <x-ui.navlist.item 
                                label="Education (Pins)"
                                icon="academic-cap"
                                :href="route('admin.education.index')"
                            />
                            
                    </x-ui.navlist.group>
                    

                    <x-ui.navlist.group 
                        label="Management"
                        collapsable
                    >
                        <x-ui.navlist.item 
                            label="Transactions"
                            icon="arrows-right-left"
                            :href="route('admin.transactions.index')"
                        />
                        <x-ui.navlist.item 
                            label="Pages"
                            icon="document-text"
                            :href="route('admin.pages.index')"
                            :active="request()->routeIs('admin.pages.*')"
                        />
                        <x-ui.navlist.item 
                            label="Shares"
                            icon="currency-dollar"
                            :href="route('admin.shares.index')"
                        />

                        <x-ui.navlist.item 
                            label="Dividends"
                            icon="banknotes"
                            :href="route('admin.dividends.index')"
                        />

                         <x-ui.navlist.item 
                            label="Loans"
                            icon="banknotes"
                            :href="route('admin.loans.index')"
                        />
                        
                        <x-ui.navlist.item 
                            label="Loan Levels"
                            icon="chart-bar-square"
                            :href="route('admin.loan-levels.index')"
                        />

                        <x-ui.navlist.item 
                            label="Brands"
                            icon="tag"
                            :href="route('admin.brands.index')"
                        />
                        
                        <x-ui.navlist.item 
                            label="Users"
                            icon="users"
                            :href="route('admin.users.index')"
                            :active="request()->routeIs('admin.users.*')"
                        />

                        <x-ui.navlist.item 
                            label="Wallets"
                            icon="wallet"
                            :href="route('admin.wallets.index')"
                            :active="request()->routeIs('admin.wallets.*')"
                        />
                        
                        <x-ui.navlist.item 
                            label="KYC Verification"
                            icon="shield-check"
                            :href="route('admin.kyc.index')"
                            :active="request()->routeIs('admin.kyc.*')"
                        />
                        
                      
                    </x-ui.navlist.group>

                    <x-ui.navlist.group 
                        label="Support"
                        collapsable
                    >
                        <x-ui.navlist.item 
                            label="Tickets"
                            icon="ticket"
                            :href="route('admin.tickets.index')"
                            :active="request()->routeIs('admin.tickets.*')"
                        />
                        <x-ui.navlist.item 
                            label="Mail"
                            icon="envelope"
                            :href="route('admin.mail.index')"
                            :active="request()->routeIs('admin.mail.*')"
                        />
                    </x-ui.navlist.group>
                    
                    <x-ui.navlist.group 
                        label="Tools and Settings"
                        collapsable
                    >
                        <x-ui.navlist.item 
                            label="User Dashboard"
                            icon="home"
                            :href="route('dashboard')"
                        />
                        
                        <x-ui.navlist.item 
                            label="Settings"
                            icon="cog"
                            :href="route('admin.settings.general')"
                            :active="request()->routeIs('admin.settings.*')"
                        />
                        <x-ui.navlist.item 
                            label="Developers"
                            icon="code-bracket"
                            href="/developers"
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
                            badge="3"
                            badge:color="orange"
                            badge:variant="outline"
                            :href="route('profile.edit')"
                        />
                        <x-ui.dropdown>
                            <x-slot:button>
                                <x-ui.navbar.item 
                                    icon="shopping-bag"
                                    icon:variant="min" 
                                    label="Store" 
                                />
                            </x-slot:button>
                            
                            <x-slot:menu>
                                <x-ui.dropdown.item icon="shopping-bag" :href="route('shares.index')">
                                    Products
                                </x-ui.dropdown.item>
                                <x-ui.dropdown.item icon="receipt-percent" :href="route('transactions.index')">
                                    Orders
                                </x-ui.dropdown.item>
                                <x-ui.dropdown.item icon="users" href="/customers">
                                    Customers
                                </x-ui.dropdown.item>
                                <x-ui.dropdown.item icon="ticket" href="/discounts">
                                    Discounts
                                </x-ui.dropdown.item>
                            </x-slot:menu>
                        </x-ui.dropdown>
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
        @livewireScriptConfig
    @vite(['resources/js/app.js'])
    
    <!-- Ensure dark mode is applied after scripts load, this is also required to prevent flickering when many livewire component changes indepently -->
    <script>
        loadDarkMode()
    </script>
    </body>
</html>