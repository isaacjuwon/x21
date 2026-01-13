<?php

use App\Actions\GenerateReferenceAction;
use App\Actions\Services\DataPurchaseAction;
use App\Livewire\Concerns\HasToast;
use App\Livewire\Concerns\WithConfirmation;
use App\Models\Brand;
use App\Models\DataPlan;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;

new class extends Component
{
    use HasToast, WithConfirmation;

    #[Rule('required')]
    public string|int $network_id = '';

    #[Rule('required')]
    public string $data_type = '';

    #[Rule('required')]
    public string|int $plan_id = '';

    #[Rule('required|min:10|max:15')]
    public $phone;

    public $plan;

    #[On('form-confirmed-purchase')]
    public function save(DataPurchaseAction $dataPurchaseAction, GenerateReferenceAction $generateReferenceAction)
    {
        $this->validate();

        if (! $this->ensureConfirmation('purchase')) {
            return;
        }

        // Get the selected plan
        $plan = DataPlan::find($this->plan_id);

        if (! $plan) {
            $this->toastError('Selected data plan not found.');

            return;
        }

        // Prepare data for the action
        $data = [
            'network' => $this->selectedNetwork->name,
            'phone' => $this->phone,
            'amount' => $plan->price,
            'reference' => $generateReferenceAction->handle('DATA'),
            'plan_code' => $plan->code,
            'ported' => false,
            'plan_id' => $this->plan_id,
        ];

        // Call the action
        $result = $dataPurchaseAction->handle($data);

        if ($result->isError()) {
            $this->toastError($result->error->getMessage());

            return;
        }

        // If we get here, the result is OK
        $responseData = $result->unwrap();
        $this->toastSuccess($responseData['message'] ?? 'Data purchase successful.');

        // Reset form fields
        $this->reset(['network_id', 'data_type', 'plan_id', 'phone']);
    }

    #[Computed]
    public function selectedNetwork()
    {
        return $this->networks->firstWhere('id', $this->network_id);
    }

    #[Computed]
    public function networks()
    {
        return Brand::active()
            ->whereHas('dataPlans', function ($query) {
                $query->where('status', true);
            })
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function dataTypes()
    {
        if (! $this->network_id) {
            return collect();
        }

        return DataPlan::where('brand_id', $this->network_id)
            ->where('status', true)
            ->distinct()
            ->unique('type')
            ->pluck('type')
            ->filter()
            ->values();
    }

    #[Computed]
    public function plans()
    {
        if (! $this->network_id || ! $this->data_type) {
            return collect();
        }

        return DataPlan::where('brand_id', $this->network_id)
            ->where('type', $this->data_type)
            ->where('status', true)
            ->orderBy('size')
            ->get();
    }

    public function updated($property, $value): void
    {
        if ($property === 'network_id') {
            $this->data_type = '';
            $this->plan_id = '';
            $this->plan = null;
        }

        if ($property === 'data_type') {
            $this->plan_id = '';
            $this->plan = null;
        }

        if ($property === 'plan_id') {
            $this->plan = DataPlan::find($value);
        }
    }

    public function render()
    {
        return $this->view()
            ->title('Purchase Data')
            ->layout('layouts::app');
    }
}; ?>


<div class="max-w-4xl mx-auto p-6" x-data="{ 
    selectedNetwork: @entangle('network_id'), 
    selectedDataType: @entangle('data_type'),
    selectedPlan: @entangle('plan_id')
}">
    <x-page-header 
        heading="Data Bundle" 
        description="Select a network and choose the best data plan for your needs"
    />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Form Content -->
        <div class="lg:col-span-2">
            <div data-slot="card" class="p-6 bg-background-content rounded-3xl border border-border space-y-8">
                <!-- Step 1: Network Selection -->
                <section class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-foreground">1. Select Network</h3>
                        @if($this->network_id)
                            <span class="text-xs text-primary font-medium flex items-center">
                                <x-ui.icon name="check-circle" class="size-4 mr-1" />
                                {{ $this->selectedNetwork?->name }}
                            </span>
                        @endif
                    </div>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        @foreach ($this->networks as $network)
                            <button 
                                type="button"
                                wire:click="$set('network_id', {{ $network->id }})"
                                @class([
                                    'relative flex flex-col items-center p-4 rounded-2xl border-2 transition-all group',
                                    'border-primary bg-primary/5 ring-4 ring-primary/10' => $this->network_id == $network->id,
                                    'border-border bg-background hover:border-primary/50' => $this->network_id != $network->id
                                ])
                            >
                                <div class="size-12 rounded-xl overflow-hidden mb-2 group-hover:scale-110 transition-transform">
                                    <img src="{{ $network->image_url }}" alt="{{ $network->name }}" class="size-full object-cover">
                                </div>
                                <span @class([
                                    'text-xs font-bold uppercase tracking-tight',
                                    'text-primary' => $this->network_id == $network->id,
                                    'text-foreground-content' => $this->network_id != $network->id
                                ])>{{ $network->name }}</span>
                                
                                @if($this->network_id == $network->id)
                                    <div class="absolute -top-2 -right-2 size-6 bg-primary text-white rounded-full flex items-center justify-center shadow-lg">
                                        <x-ui.icon name="check" class="size-4" />
                                    </div>
                                @endif
                            </button>
                        @endforeach
                    </div>
                    @error('network_id')
                        <p class="mt-1 text-[10px] text-red-500 font-bold uppercase tracking-wider flex items-center gap-1">
                            <x-ui.icon name="exclamation-circle" class="w-3 h-3" />
                            {{ $message }}
                        </p>
                    @enderror
                </section>

                <!-- Step 2: Data Type Selection -->
                <section @class(['space-y-4 transition-all duration-500', 'opacity-50 pointer-events-none' => !$this->network_id])>
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-foreground">2. Type</h3>
                        @if($this->data_type)
                            <span class="text-xs text-primary font-bold uppercase">{{ $this->data_type }}</span>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @forelse ($this->dataTypes as $type)
                            <button 
                                type="button"
                                wire:click="$set('data_type', '{{ $type }}')"
                                @class([
                                    'px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider border-2 transition-all',
                                    'border-primary bg-primary text-white shadow-md' => $this->data_type == $type,
                                    'border-border text-foreground-content bg-background hover:border-primary/50' => $this->data_type != $type
                                ])
                            >
                                {{ $type }}
                            </button>
                        @empty
                            <p class="text-xs text-foreground-content italic">Select a network first</p>
                        @endforelse
                    </div>
                    @error('data_type')
                        <p class="mt-1 text-[10px] text-red-500 font-bold uppercase tracking-wider flex items-center gap-1">
                            <x-ui.icon name="exclamation-circle" class="w-3 h-3" />
                            {{ $message }}
                        </p>
                    @enderror
                </section>

                <!-- Step 3: Plan Selection -->
                <section @class(['space-y-4 transition-all duration-500', 'opacity-50 pointer-events-none' => !$this->data_type])>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-foreground">3. Select Plan</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @forelse ($this->plans as $p)
                            <button 
                                type="button"
                                wire:click="$set('plan_id', {{ $p->id }})"
                                @class([
                                    'flex items-center justify-between p-4 rounded-xl border-2 text-left transition-all',
                                    'border-primary bg-primary/5 ring-2 ring-primary/5' => $this->plan_id == $p->id,
                                    'border-border bg-background hover:border-primary/50' => $this->plan_id != $p->id
                                ])
                            >
                                <div class="space-y-1">
                                    <p @class([
                                        'text-sm font-bold',
                                        'text-primary' => $this->plan_id == $p->id,
                                        'text-foreground' => $this->plan_id != $p->id
                                    ])>{{ $p->name }}</p>
                                    <p class="text-[10px] text-foreground-content font-medium uppercase">{{ $p->validity ?? '30 Days' }} • {{ $p->size }}</p>
                                </div>
                                <span @class([
                                    'text-sm font-black',
                                    'text-primary' => $this->plan_id == $p->id,
                                    'text-foreground-content' => $this->plan_id != $p->id
                                ])>{{ Number::currency($p->price) }}</span>
                            </button>
                        @empty
                            <div class="col-span-full py-8 text-center bg-background rounded-2xl border-2 border-dashed border-border">
                                <x-ui.icon name="cube" class="size-8 mx-auto text-foreground-content/30 mb-2" />
                                <p class="text-xs text-foreground-content">Choose a data type to view plans</p>
                            </div>
                        @endforelse
                    </div>
                    @error('plan_id')
                        <p class="mt-1 text-[10px] text-red-500 font-bold uppercase tracking-wider flex items-center gap-1">
                            <x-ui.icon name="exclamation-circle" class="w-3 h-3" />
                            {{ $message }}
                        </p>
                    @enderror
                </section>

                <!-- Step 4: Phone Number -->
                <section @class(['space-y-4 transition-all duration-500', 'opacity-50 pointer-events-none' => !$this->plan_id])>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-foreground">4. Recipient details</h3>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-foreground-content">
                            <x-ui.icon name="device-phone-mobile" class="size-5" />
                        </div>
                        <input 
                            type="tel" 
                            wire:model.live="phone"
                            placeholder="Enter phone number" 
                            @class([
                                'w-full pl-12 pr-4 py-4 bg-background border-2 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-lg font-bold tracking-widest placeholder:text-foreground-content/50',
                                'border-border focus:border-primary' => !$errors->has('phone'),
                                'border-error focus:border-error' => $errors->has('phone'),
                            ])
                        >
                    </div>
                    @error('phone')
                        <p class="mt-1 text-[10px] text-red-500 font-bold uppercase tracking-wider flex items-center gap-1">
                            <x-ui.icon name="exclamation-circle" class="w-3 h-3" />
                            {{ $message }}
                        </p>
                    @enderror
                </section>
            </div>
        </div>

        <!-- Sidebar Summary -->
        <div class="lg:col-span-1">
            <div class="sticky top-24 space-y-6">
                <div class="bg-background-content rounded-3xl shadow-xl overflow-hidden border border-border">
                    <div class="p-6 bg-primary border-b border-primary-fg/10">
                        <h4 class="text-primary-fg font-bold uppercase tracking-widest text-xs">Selection Summary</h4>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        @if($this->selectedNetwork)
                            <div class="flex items-center gap-4">
                                <img src="{{ $this->selectedNetwork->image_url }}" alt="" class="size-12 rounded-xl object-cover shadow-md">
                                <div>
                                    <p class="text-xs text-foreground-content font-bold uppercase tracking-wider">Network</p>
                                    <p class="font-black text-foreground uppercase">{{ $this->selectedNetwork->name }}</p>
                                </div>
                            </div>
                        @endif

                        @if($this->plan)
                            <div class="space-y-1">
                                <p class="text-xs text-foreground-content font-bold uppercase tracking-wider">Plan Details</p>
                                <p class="font-black text-foreground text-lg leading-tight">{{ $this->plan->name }}</p>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 bg-secondary/10 rounded text-[10px] font-bold text-secondary uppercase tracking-tighter">{{ $this->data_type }}</span>
                                    <span class="text-xs text-foreground-content/70 font-medium">Auto-renewal inclusive</span>
                                </div>
                            </div>

                            <div class="pt-6 border-t border-border">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-foreground-content font-bold text-xs uppercase">Total Price</span>
                                    <span class="text-2xl font-black text-primary">{{ Number::currency($this->plan->price) }}</span>
                                </div>
                            </div>
                        @else
                            <div class="py-12 text-center">
                                <div class="size-16 bg-background rounded-2xl flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-border">
                                    <x-ui.icon name="shopping-bag" class="size-8 text-foreground-content/30" />
                                </div>
                                <p class="text-xs text-foreground-content font-medium max-w-[150px] mx-auto">Selected items will appear here for review</p>
                            </div>
                        @endif

                        <x-ui.button 
                            wire:click="save"
                            variant="primary" 
                            class="w-full h-14 rounded-2xl font-black uppercase tracking-widest text-sm shadow-lg shadow-primary/20 hover:shadow-primary/40 disabled:opacity-50 disabled:grayscale transition-all"
                            :disabled="!$this->plan_id || !$this->phone"
                        >
                            <span>Purchase Now</span>
                            <x-ui.icon name="arrow-right" class="size-5 ml-2" />
                        </x-ui.button>
                        
                        <p class="text-[10px] text-foreground-content text-center font-medium leading-relaxed">
                            By clicking purchase, you agree to our Terms of Service and Privacy Policy. Funds will be deducted from your wallet balance.
                        </p>
                    </div>
                </div>

                <!-- Wallet info quick display -->
                <div class="bg-accent rounded-3xl p-5 text-white shadow-lg overflow-hidden relative group">
                    <div class="relative z-10">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-primary-fg/80 mb-1">Available Funds</p>
                        <p class="text-xl font-black">{{ Number::currency(auth()->user()->wallet_balance) }}</p>
                    </div>
                    <x-ui.icon name="wallet" class="absolute -right-6 -bottom-6 size-28 text-white/10 group-hover:scale-110 transition-transform" />
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal Refined -->
    <x-ui.modal id="confirm-purchase" heading="Review Purchase">
        <div class="space-y-6">
            <div class="p-4 bg-background rounded-3xl border border-border">
                <div class="flex items-center gap-4 mb-6">
                    <div class="size-16 rounded-2xl bg-background-content p-2 shadow-sm border border-border">
                        <img src="{{ $this->selectedNetwork?->image_url }}" alt="" class="size-full object-cover rounded-lg">
                    </div>
                    <div>
                        <p class="text-xs font-bold text-foreground-content uppercase tracking-widest">Selected Item</p>
                        <h4 class="text-xl font-black text-foreground leading-tight">{{ $this->plan?->name }}</h4>
                        <span class="text-xs font-medium text-primary">{{ $this->selectedNetwork?->name }} • {{ $this->data_type }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-y-4">
                    <div class="space-y-0.5">
                        <p class="text-[10px] font-bold text-foreground-content uppercase tracking-widest">Recipient Number</p>
                        <p class="text-lg font-black tracking-widest text-foreground">{{ $this->phone }}</p>
                    </div>
                    <div class="space-y-0.5 text-right">
                        <p class="text-[10px] font-bold text-foreground-content uppercase tracking-widest">Transaction Fee</p>
                        <p class="text-lg font-black text-foreground uppercase">FREE</p>
                    </div>
                    <div class="col-span-2 pt-4 border-t border-border flex items-center justify-between">
                        <p class="text-sm font-bold text-foreground-content uppercase">Grand Total</p>
                        <p class="text-2xl font-black text-primary">{{ Number::currency($this->plan?->price ?? 0) }}</p>
                    </div>
                </div>
            </div>

            <x-ui.alerts type="warning" class="rounded-2xl text-xs">
                Please double-check the phone number. Transactions are instant and irreversible in most cases.
            </x-ui.alerts>
        </div>
        
        <x-slot name="footer">
            <div class="flex gap-3 w-full">
                <x-ui.button x-on:click="$data.close();" variant="outline" class="flex-1 h-12 rounded-xl font-bold uppercase tracking-widest text-xs">
                    Go Back
                </x-ui.button>
                <x-ui.button x-on:click="$wire.confirmation()" variant="primary" class="flex-1 h-12 rounded-xl font-black uppercase tracking-widest text-xs shadow-lg shadow-primary/20">
                    Confirm & Buy
                </x-ui.button>
            </div>
        </x-slot>
    </x-ui.modal>
</div>
