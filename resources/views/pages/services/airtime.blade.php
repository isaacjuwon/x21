<?php

use App\Actions\GenerateReferenceAction;
use App\Actions\Services\AirtimePurchaseAction;
use App\Events\Services\ServicePurchased;
use App\Livewire\Concerns\HasToast;
use App\Livewire\Concerns\WithConfirmation;
use App\Models\AirtimePlan;
use App\Models\Brand;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;

new class extends Component
{
    use HasToast, WithConfirmation;

    #[Rule('required|numeric|min:100')]
    public float|int $amount = 0;

    #[Rule('required')]
    public string|int $network_id = '';

    public ?AirtimePlan $plan = null;

    #[Rule('required|min:10|max:15')]
    public $phone;

    #[On('form-confirmed-purchase')]
    public function save(AirtimePurchaseAction $airtimePurchaseAction, GenerateReferenceAction $generateReferenceAction)
    {

        $this->validate();

        if (! $this->ensureConfirmation('purchase')) {
            return;
        }

        // Prepare data for the action
        $data = [
            'network' => $this->selectedNetwork->name, // Assuming name is the network identifier
            'phone' => $this->phone,
            'amount' => $this->amount,
            'reference' => $generateReferenceAction->handle('AIRTIME'), // Generate reference
            'ported' => false, // Assuming default to false, or add form input
            'plan_id' => $this->plan?->id,
        ];

        // Call the action
        $result = $airtimePurchaseAction->handle($data);

        if ($result->isError()) {

            $this->toastError($result->error->getMessage());

            return;
        }

        // If we get here, the result is OK
        $responseData = $result->unwrap();

        // Dispatch notification event
        ServicePurchased::dispatch(
            user: auth()->user(),
            serviceType: 'airtime',
            productName: "{$this->selectedNetwork->name} Airtime - ₦".number_format($this->amount, 2),
            amount: $this->amount,
            transactionReference: $data['reference'],
            transactionId: $responseData['transaction_id'] ?? null
        );

        $this->toastSuccess($responseData['message'] ?? 'Airtime purchase successful.');

        // Reset form fields
        $this->reset(['amount', 'network_id', 'phone']);
    }

    #[Computed]
    public function networks()
    {
        return Brand::active()
            ->whereHas('airtimePlans', function ($query) {
                $query->where('status', true);
            })
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function selectedNetwork()
    {
        if (empty($this->network_id)) {
            return null;
        }

        return $this->networks->firstWhere('id', $this->network_id);
    }

    public function updated($property, $value): void
    {
        if ($property === 'network_id' && ! empty($value)) {
            $this->plan = AirtimePlan::where('brand_id', $value)->first();
        }
    }

    public function render()
    {
        return $this->view()
            ->title('Purchase Airtime')
            ->layout('layouts::app');
    }
}; ?>

<div class="max-w-4xl mx-auto p-6" x-data="{ amount: @entangle('amount') }">
    <x-page-header 
        heading="Airtime Recharge" 
        description="Fast and reliable airtime top-up for any network"
    />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Form Content -->
        <div class="lg:col-span-2 space-y-8">
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
                                'border-border bg-background-content hover:border-primary/50' => $this->network_id != $network->id
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

            <!-- Step 2: Recipient details -->
            <section @class(['space-y-4 transition-all duration-500', 'opacity-50 pointer-events-none' => !$this->network_id])>
                <h3 class="text-sm font-bold uppercase tracking-wider text-foreground">2. Recipient details</h3>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-foreground-content">
                        <x-ui.icon name="device-phone-mobile" class="size-5" />
                    </div>
                    <input 
                        type="tel" 
                        wire:model.live="phone"
                        placeholder="Enter phone number" 
                        @class([
                            'w-full pl-12 pr-4 py-4 bg-background-content border-2 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-lg font-bold tracking-widest placeholder:text-foreground-content/50',
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

            <!-- Step 3: Amount -->
            <section @class(['space-y-4 transition-all duration-500', 'opacity-50 pointer-events-none' => !$this->phone])>
                <h3 class="text-sm font-bold uppercase tracking-wider text-foreground">3. Amount</h3>
                
                <div class="space-y-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-primary font-black text-xl">
                            ₦
                        </div>
                        <input 
                            type="number" 
                            wire:model.live="amount"
                            placeholder="0.00" 
                            @class([
                                'w-full pl-10 pr-4 py-4 bg-background-content border-2 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-2xl font-black placeholder:text-foreground-content/30',
                                'border-border focus:border-primary' => !$errors->has('amount'),
                                'border-error focus:border-error' => $errors->has('amount'),
                            ])
                        >
                    </div>

                    <div class="grid grid-cols-4 gap-2">
                        @foreach([100, 200, 500, 1000] as $preset)
                            <button 
                                type="button"
                                @click="amount = {{ $preset }}"
                                class="py-2.5 rounded-xl border border-border bg-background-content text-xs font-bold text-foreground-content hover:border-primary hover:text-primary hover:bg-primary/5 transition-all"
                            >
                                ₦{{ number_format($preset) }}
                            </button>
                        @endforeach
                    </div>
                </div>
                @error('amount')
                    <p class="mt-1 text-[10px] text-red-500 font-bold uppercase tracking-wider flex items-center gap-1">
                        <x-ui.icon name="exclamation-circle" class="w-3 h-3" />
                        {{ $message }}
                    </p>
                @enderror
            </section>
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

                        @if($this->amount > 0)
                            <div class="space-y-1">
                                <p class="text-xs text-foreground-content font-bold uppercase tracking-wider">Recharge Amount</p>
                                <p class="font-black text-foreground text-3xl leading-tight">{{ Number::currency($this->amount) }}</p>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 bg-success/10 rounded text-[10px] font-bold text-success uppercase tracking-tighter">Instant Delivery</span>
                                </div>
                            </div>
                        @else
                            <div class="py-12 text-center">
                                <div class="size-16 bg-background rounded-2xl flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-border">
                                    <x-ui.icon name="bolt" class="size-8 text-foreground-content/30" />
                                </div>
                                <p class="text-xs text-foreground-content font-medium max-w-[150px] mx-auto">Recharge details will appear here for review</p>
                            </div>
                        @endif

                        <x-ui.button 
                            icon="arrow-right"
                            wire:click="save"
                            variant="primary" 
                            class="w-full h-14 rounded-2xl font-black uppercase tracking-widest text-sm shadow-lg shadow-primary/20 hover:shadow-primary/40 disabled:opacity-50 disabled:grayscale transition-all"
                            :disabled="!$this->network_id || !$this->phone || $this->amount < 100"
                        >
                            <span>Purchase Now</span>
                        </x-ui.button>
                        
                        <p class="text-[10px] text-foreground-content text-center font-medium leading-relaxed">
                            Secured transaction. Funds will be deducted from your wallet balance instantly.
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
    <x-ui.modal id="confirm-purchase" heading="Review Order">
        <div class="space-y-6">
            <div class="p-4 bg-background rounded-3xl border border-border">
                <div class="flex items-center gap-4 mb-6">
                    <div class="size-16 rounded-2xl bg-background-content p-2 shadow-sm border border-border">
                        <img src="{{ $this->selectedNetwork?->image_url }}" alt="" class="size-full object-cover rounded-lg">
                    </div>
                    <div>
                        <p class="text-xs font-bold text-foreground-content uppercase tracking-widest">Selected Item</p>
                        <h4 class="text-xl font-black text-foreground leading-tight">Airtime Recharge</h4>
                        <span class="text-xs font-medium text-primary">{{ $this->selectedNetwork?->name }} Network</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-y-4">
                    <div class="space-y-0.5">
                        <p class="text-[10px] font-bold text-foreground-content uppercase tracking-widest">Recipient Number</p>
                        <p class="text-lg font-black tracking-widest text-foreground">{{ $this->phone }}</p>
                    </div>
                    <div class="space-y-0.5 text-right">
                        <p class="text-[10px] font-bold text-foreground-content uppercase tracking-widest">Processing</p>
                        <p class="text-lg font-black text-success uppercase">INSTANT</p>
                    </div>
                    <div class="col-span-2 pt-4 border-t border-border flex items-center justify-between">
                        <p class="text-sm font-bold text-foreground-content uppercase">Grand Total</p>
                        <p class="text-2xl font-black text-primary">{{ Number::currency($this->amount) }}</p>
                    </div>
                </div>
            </div>

            <x-ui.alerts type="warning" class="rounded-2xl text-xs">
                Ensure the phone number <strong>{{ $this->phone }}</strong> is correct. Airtime transfers cannot be reversed.
            </x-ui.alerts>
        </div>
        
        <x-slot name="footer">
            <div class="flex gap-3 w-full">
                <x-ui.button x-on:click="$data.close();" variant="outline" class="flex-1 h-12 rounded-xl font-bold uppercase tracking-widest text-xs">
                    Cancel
                </x-ui.button>
                <x-ui.button x-on:click="$wire.confirmation()" variant="primary" class="flex-1 h-12 rounded-xl font-black uppercase tracking-widest text-xs shadow-lg shadow-primary/20">
                    Confirm & Buy
                </x-ui.button>
            </div>
        </x-slot>
    </x-ui.modal>
</div>