<?php

namespace App\Livewire\Pages\Properties;

use App\Models\Property;
use App\Models\Enquiry;
use App\Livewire\Concerns\HasToast;
use Livewire\Attributes\Rule;
use Livewire\Component;

new class extends Component
{
    use HasToast;

    public Property $property;

    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('required|email|max:255')]
    public string $email = '';

    #[Rule('nullable|string|max:255')]
    public string $phone = '';

    #[Rule('required|string|min:10')]
    public string $message = '';

    public function mount(Property $property)
    {
        $this->property = $property;
        
        if (auth()->check()) {
            $this->name = auth()->user()->name;
            $this->email = auth()->user()->email;
        }
    }

    public function sendEnquiry()
    {
        $this->validate();

        Enquiry::create([
            'property_id' => $this->property->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => $this->message,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'status' => \App\Enums\EnquiryStatus::Pending,
        ]);

        $this->reset(['message']);
        $this->toastSuccess('Your enquiry has been sent successfully! Our agent will contact you soon.');
    }

    public function render()
    {
        return $this->view()->layout('layouts::app');
    }
}; ?>

<div class="max-w-7xl mx-auto p-4 md:p-6 space-y-8">
    <!-- Breadcrumbs/Back -->
    <div class="flex items-center gap-2">
        <x-ui.button tag="a" href="{{ route('properties.listing') }}" variant="ghost" size="sm" class="text-[10px] font-bold uppercase tracking-widest">
            <x-ui.icon name="arrow-left" class="w-3 h-3 mr-1" />
            Back to Listings
        </x-ui.button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content (Left) -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Image Gallery Placeholder -->
            <div class="bg-neutral-900 rounded-[2rem] overflow-hidden aspect-video relative group border border-neutral-100 dark:border-neutral-800 shadow-2xl">
                @if($property->main_image)
                    <img src="{{ $property->main_image }}" class="w-full h-full object-cover" alt="{{ $property->title }}">
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center gap-4">
                        <x-ui.icon name="photo" class="w-20 h-20 text-neutral-800" />
                        <span class="text-xs font-bold text-neutral-600 uppercase tracking-[0.2em]">Listing Media Unavailable</span>
                    </div>
                @endif
                
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 to-transparent p-8">
                    <div class="flex flex-wrap items-center gap-3">
                        <x-ui.badge color="primary" class="font-black uppercase tracking-tighter ring-2 ring-white/10 backdrop-blur-md">
                            {{ $property->listing_type->getLabel() }}
                        </x-ui.badge>
                        <x-ui.badge color="secondary" class="font-black uppercase tracking-tighter ring-2 ring-white/10 backdrop-blur-md">
                            {{ $property->type->getLabel() }}
                        </x-ui.badge>
                    </div>
                </div>
            </div>

            <!-- Property Title & Price -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div class="space-y-2">
                    <h1 class="text-4xl font-black text-neutral-900 dark:text-white leading-tight tracking-tight">{{ $property->title }}</h1>
                    <div class="flex items-center text-sm font-bold text-neutral-500 uppercase tracking-widest">
                        <x-ui.icon name="map-pin" class="w-4 h-4 mr-1.5 text-primary" />
                        {{ $property->address }}, {{ $property->city }}, {{ $property->state }}
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-[10px] font-black text-neutral-400 uppercase tracking-[0.3em] mb-1">Asking Price</div>
                    <div class="text-4xl font-black text-primary tracking-tighter">
                        {{ $property->formatted_price }}
                    </div>
                </div>
            </div>

            <!-- Vital Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 py-8 border-y border-neutral-100 dark:border-neutral-800">
                <div class="flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                        <x-ui.icon name="square-2-stack" class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Bedrooms</div>
                        <div class="text-lg font-black text-neutral-900 dark:text-white">{{ $property->bedrooms }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-2xl bg-secondary/10 flex items-center justify-center text-secondary group-hover:scale-110 transition-transform">
                        <x-ui.icon name="swatch" class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Bathrooms</div>
                        <div class="text-lg font-black text-neutral-900 dark:text-white">{{ $property->bathrooms }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-2xl bg-success/10 flex items-center justify-center text-success group-hover:scale-110 transition-transform">
                        <x-ui.icon name="arrows-pointing-out" class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Area</div>
                        <div class="text-lg font-black text-neutral-900 dark:text-white">{{ number_format($property->area_sqft) }} <span class="text-xs text-neutral-500 font-bold uppercase">sqft</span></div>
                    </div>
                </div>
                <div class="flex items-center gap-4 group">
                    <div class="w-12 h-12 rounded-2xl bg-warning/10 flex items-center justify-center text-warning group-hover:scale-110 transition-transform">
                        <x-ui.icon name="calendar-days" class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-neutral-400 uppercase tracking-widest">Year Built</div>
                        <div class="text-lg font-black text-neutral-900 dark:text-white">{{ $property->year_built ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="space-y-4">
                <h2 class="text-xl font-black text-neutral-900 dark:text-white uppercase tracking-tighter">Property Description</h2>
                <div class="prose prose-sm dark:prose-invert max-w-none text-neutral-600 dark:text-neutral-400 leading-relaxed font-semibold">
                    {{ $property->description }}
                </div>
            </div>

            <!-- Features -->
            <div class="space-y-6">
                <h2 class="text-xl font-black text-neutral-900 dark:text-white uppercase tracking-tighter">Premium Features</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex items-center gap-3 p-4 rounded-2xl bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-100 dark:border-neutral-700/50">
                        <div @class(['p-1.5 rounded-full ring-4', $property->has_garage ? 'bg-success ring-success/10 text-white' : 'bg-neutral-200 dark:bg-neutral-700 ring-neutral-200/5 dark:ring-neutral-700/5 text-neutral-400'])>
                            <x-ui.icon name="check" class="w-3 h-3" />
                        </div>
                        <span @class(['text-xs font-bold uppercase tracking-widest', $property->has_garage ? 'text-neutral-900 dark:text-white' : 'text-neutral-400'])>Private Garage</span>
                    </div>
                    <div class="flex items-center gap-3 p-4 rounded-2xl bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-100 dark:border-neutral-700/50">
                        <div @class(['p-1.5 rounded-full ring-4', $property->is_furnished ? 'bg-success ring-success/10 text-white' : 'bg-neutral-200 dark:bg-neutral-700 ring-neutral-200/5 dark:ring-neutral-700/5 text-neutral-400'])>
                            <x-ui.icon name="check" class="w-3 h-3" />
                        </div>
                        <span @class(['text-xs font-bold uppercase tracking-widest', $property->is_furnished ? 'text-neutral-900 dark:text-white' : 'text-neutral-400'])>Fully Furnished</span>
                    </div>
                    @if($property->parking_spaces > 0)
                        <div class="flex items-center gap-3 p-4 rounded-2xl bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-100 dark:border-neutral-700/50">
                            <div class="p-1.5 rounded-full bg-success ring-4 ring-success/10 text-white">
                                <x-ui.icon name="check" class="w-3 h-3" />
                            </div>
                            <span class="text-xs font-bold uppercase tracking-widest text-neutral-900 dark:text-white">{{ $property->parking_spaces }} Parking Spaces</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Actions (Right) -->
        <div class="space-y-8">
            <!-- Enquiry Form Card -->
            <div class="bg-white dark:bg-neutral-800 p-8 rounded-[2.5rem] shadow-2xl border border-neutral-100 dark:border-neutral-700 sticky top-12">
                <div class="text-center space-y-2 mb-8">
                    <div class="inline-flex items-center px-3 py-1 bg-primary/5 text-primary rounded-full text-[10px] font-black uppercase tracking-[0.2em]">Contact Agent</div>
                    <h3 class="text-2xl font-black text-neutral-900 dark:text-white tracking-tight">Make an Inquiry</h3>
                    <p class="text-xs font-bold text-neutral-500 uppercase tracking-widest">We'll get back to you within 24 hours</p>
                </div>

                <form wire:submit="sendEnquiry" class="space-y-5">
                    <x-ui.field>
                        <x-ui.label for="name" class="text-[10px] font-black uppercase tracking-widest text-neutral-400">Full Name</x-ui.label>
                        <x-ui.input wire:model="name" id="name" placeholder="John Doe" class="font-bold border-0 bg-neutral-50 dark:bg-neutral-900 px-4 h-12" />
                        <x-ui.error name="name" />
                    </x-ui.field>

                    <x-ui.field>
                        <x-ui.label for="email" class="text-[10px] font-black uppercase tracking-widest text-neutral-400">Email Address</x-ui.label>
                        <x-ui.input wire:model="email" id="email" type="email" placeholder="john@example.com" class="font-bold border-0 bg-neutral-50 dark:bg-neutral-900 px-4 h-12" />
                        <x-ui.error name="email" />
                    </x-ui.field>

                    <x-ui.field>
                        <x-ui.label for="phone" class="text-[10px] font-black uppercase tracking-widest text-neutral-400">Phone Number (Optional)</x-ui.label>
                        <x-ui.input wire:model="phone" id="phone" placeholder="+234 ..." class="font-bold border-0 bg-neutral-50 dark:bg-neutral-900 px-4 h-12" />
                        <x-ui.error name="phone" />
                    </x-ui.field>

                    <x-ui.field>
                        <x-ui.label for="message" class="text-[10px] font-black uppercase tracking-widest text-neutral-400">Message</x-ui.label>
                        <x-ui.textarea wire:model="message" id="message" rows="4" placeholder="I'm interested in this property and would like to..." class="font-bold border-0 bg-neutral-50 dark:bg-neutral-900 px-4 py-3" />
                        <x-ui.error name="message" />
                    </x-ui.field>

                    <div class="pt-4">
                        <x-ui.button type="submit" variant="primary" size="lg" class="w-full font-black uppercase tracking-[0.2em] shadow-lg shadow-primary/20 h-14">
                            Send Inquiry
                        </x-ui.button>
                    </div>
                </form>
                
                <div class="mt-8 pt-6 border-t border-neutral-100 dark:border-neutral-700 flex items-center justify-between text-[10px] font-bold text-neutral-500 uppercase tracking-widest">
                    <span>Property Ref: #{{ str_pad($property->id, 5, '0', STR_PAD_LEFT) }}</span>
                    <div class="flex gap-2">
                        <x-ui.icon name="share" class="w-3.5 h-3.5 hover:text-primary cursor-pointer transition-colors" />
                        <x-ui.icon name="heart" class="w-3.5 h-3.5 hover:text-red-500 cursor-pointer transition-colors" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
