<?php

use App\Actions\GenerateReferenceAction;
use App\Actions\Services\ElectricityPurchaseAction;
use App\Livewire\Concerns\HasToast;
use App\Livewire\Concerns\WithConfirmation;
use App\Models\Brand;
use App\Models\ElectricityPlan;
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
    public string|int $operator_id = '';

    #[Rule('required')]
    public string $meter_type = '';

    #[Rule('required')]
    public string $meter_number = '';

    public $plan;

    #[On('form-confirmed-purchase')]
    public function save(ElectricityPurchaseAction $electricityPurchaseAction, GenerateReferenceAction $generateReferenceAction)
    {
        $this->validate();

        if (! $this->ensureConfirmation('purchase')) {
            return;
        }

        $data = [
            'network_name' => $this->selectedoperator->name,
            'phone' => $this->meter_number,
            'meter_type' => $this->meter_type,
            'amount' => $this->amount,
            'reference' => $generateReferenceAction->handle('ELECTRICITY'),
            'network_id' => $this->operator_id,
        ];

        $result = $electricityPurchaseAction->handle($data);

        if ($result->isError()) {
            $this->toastError($result->error->getMessage());

            return;
        }

        // If we get here, the result is OK
        $responseData = $result->unwrap();
        $this->toastSuccess($responseData['message'] ?? 'Electricity purchase successful.');

        // Reset form fields
        $this->reset(['amount', 'operator_id', 'meter_type', 'meter_number']);
    }

    #[Computed]
    public function selectedoperator()
    {
        return $this->operators->firstWhere('id', $this->operator_id);
    }

    #[Computed]
    public function operators()
    {
        return Brand::active()
            ->whereHas('electricityPlans', function ($query) {
                $query->where('status', true);
            })
            ->orderBy('name')
            ->get();
    }

    public function updated($property, $value): void
    {
        if ($property === 'operator_id') {
            $this->plan = ElectricityPlan::where('brand_id', $value)->first();
        }
    }

    public function render()
    {
        return $this->view()
            ->title('Electricity Bills')
            ->layout('layouts::app');
    }
}; ?>


<div class="max-w-4xl mx-auto p-6" x-data="{ 
    selectedOperator: @entangle('operator_id'), 
    meterType: @entangle('meter_type'),
    amount: @entangle('amount')
}">
    <x-page-header 
        heading="Electricity Bill" 
        description="Quickly pay your electricity bills for prepaid and postpaid meters"
    />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Form Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Step 1: Operator Selection -->
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-foreground">1. Select Distributor</h3>
                    @if($this->operator_id)
                        <span class="text-xs text-primary font-medium flex items-center">
                            <x-ui.icon name="check-circle" class="size-4 mr-1" />
                            {{ $this->selectedoperator?->name }}
                        </span>
                    @endif
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @foreach ($this->operators as $operator)
                        <button 
                            type="button"
                            wire:click="$set('operator_id', {{ $operator->id }})"
                            @class([
                                'relative flex flex-col items-center p-5 rounded-2xl border-2 transition-all group',
                                'border-primary bg-primary/5 ring-4 ring-primary/10' => $this->operator_id == $operator->id,
                                'border-border bg-background-content hover:border-primary/50' => $this->operator_id != $operator->id
                            ])
                        >
                            <div class="size-14 rounded-xl overflow-hidden mb-3 group-hover:scale-110 transition-transform">
                                <img src="{{ $operator->image_url }}" alt="{{ $operator->name }}" class="size-full object-cover">
                            </div>
                            <span @class([
                                'text-[10px] font-black uppercase tracking-wider text-center leading-tight',
                                'text-primary' => $this->operator_id == $operator->id,
                                'text-foreground-content' => $this->operator_id != $operator->id
                            ])>{{ $operator->name }}</span>
                            
                            @if($this->operator_id == $operator->id)
                                <div class="absolute -top-2 -right-2 size-6 bg-primary text-white rounded-full flex items-center justify-center shadow-lg">
                                    <x-ui.icon name="check" class="size-4" />
                                </div>
                            @endif
                        </button>
                    @endforeach
                </div>
                @error('operator_id')
                    <p class="mt-1 text-[10px] text-red-500 font-bold uppercase tracking-wider flex items-center gap-1">
                        <x-ui.icon name="exclamation-circle" class="w-3 h-3" />
                        {{ $message }}
                    </p>
                @enderror
            </section>

            <!-- Step 2: Meter Type -->
            <section @class(['space-y-4 transition-all duration-500', 'opacity-50 pointer-events-none' => !$this->operator_id])>
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-foreground">2. Meter Type</h3>
                    @if($this->meter_type)
                        <span class="text-xs text-primary font-bold uppercase">{{ $this->meter_type }}</span>
                    @endif
                </div>

                <div class="flex gap-3">
                    @foreach(['prepaid', 'postpaid'] as $type)
                        <button 
                            type="button" 
                            wire:click="$set('meter_type', '{{ $type }}')"
                            @class([
                                'flex-1 py-4 rounded-2xl border-2 font-black uppercase tracking-widest text-xs transition-all',
                                'border-primary bg-primary text-white shadow-lg shadow-primary/20' => $this->meter_type == $type,
                                'border-border text-foreground-content bg-background-content hover:border-primary/30' => $this->meter_type != $type
                            ])
                        >
                            {{ $type }}
                        </button>
                    @endforeach
                </div>
                @error('meter_type')
                    <p class="mt-1 text-[10px] text-red-500 font-bold uppercase tracking-wider flex items-center gap-1">
                        <x-ui.icon name="exclamation-circle" class="w-3 h-3" />
                        {{ $message }}
                    </p>
                @enderror
            </section>

            <!-- Step 3: Meter Number -->
            <section @class(['space-y-4 transition-all duration-500', 'opacity-50 pointer-events-none' => !$this->meter_type])>
                <h3 class="text-sm font-bold uppercase tracking-wider text-foreground">3. Meter Number</h3>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-foreground-content">
                        <x-ui.icon name="hashtag" class="size-6" />
                    </div>
                    <input 
                        type="text" 
                        wire:model.live="meter_number"
                        placeholder="Enter your meter number" 
                        @class([
                            'w-full pl-14 pr-4 py-5 bg-background-content border-2 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-xl font-bold tracking-widest placeholder:text-foreground-content/50',
                            'border-border focus:border-primary' => !$errors->has('meter_number'),
                            'border-error focus:border-error' => $errors->has('meter_number'),
                        ])
                    >
                </div>
                @error('meter_number')
                    <p class="mt-1 text-[10px] text-red-500 font-bold uppercase tracking-wider flex items-center gap-1">
                        <x-ui.icon name="exclamation-circle" class="w-3 h-3" />
                        {{ $message }}
                    </p>
                @enderror
            </section>

            <!-- Step 4: Amount -->
            <section @class(['space-y-4 transition-all duration-500', 'opacity-50 pointer-events-none' => !$this->meter_number])>
                <h3 class="text-sm font-bold uppercase tracking-wider text-foreground">4. Amount</h3>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-primary font-black text-2xl">
                        ₦
                    </div>
                    <input 
                        type="number" 
                        wire:model.live="amount"
                        placeholder="0.00" 
                        @class([
                            'w-full pl-12 pr-4 py-5 bg-background-content border-2 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-3xl font-black placeholder:text-foreground-content/30',
                            'border-border focus:border-primary' => !$errors->has('amount'),
                            'border-error focus:border-error' => $errors->has('amount'),
                        ])
                    >
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
                        <h4 class="text-primary-fg font-bold uppercase tracking-widest text-xs">Payment Summary</h4>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        @if($this->selectedoperator)
                            <div class="flex items-center gap-4">
                                <img src="{{ $this->selectedoperator->image_url }}" alt="" class="size-14 rounded-2xl object-cover shadow-md">
                                <div>
                                    <p class="text-xs text-foreground-content font-bold uppercase tracking-wider">Distributor</p>
                                    <p class="font-black text-foreground uppercase text-sm leading-tight">{{ $this->selectedoperator->name }}</p>
                                </div>
                            </div>
                        @endif

                        @if($this->amount > 0)
                            <div class="space-y-1">
                                <p class="text-xs text-foreground-content font-bold uppercase tracking-wider">Total Charge</p>
                                <p class="font-black text-foreground text-3xl leading-tight">{{ Number::currency($this->amount) }}</p>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 bg-warning/10 rounded text-[10px] font-bold text-warning uppercase tracking-tighter">{{ $this->meter_type ?? 'Meter' }}</span>
                                    <span class="text-[10px] text-foreground-content/70 font-medium">Token instant delivery</span>
                                </div>
                            </div>
                        @else
                            <div class="py-12 text-center">
                                <div class="size-16 bg-background rounded-2xl flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-border">
                                    <x-ui.icon name="light-bulb" class="size-8 text-foreground-content/30" />
                                </div>
                                <p class="text-xs text-foreground-content font-medium max-w-[150px] mx-auto">Complete the form to see your payment summary</p>
                            </div>
                        @endif

                        <x-ui.button 
                            wire:click="save"
                            variant="primary" 
                            class="w-full h-14 rounded-2xl font-black uppercase tracking-widest text-sm shadow-lg shadow-primary/20 hover:shadow-primary/40 disabled:opacity-50 disabled:grayscale transition-all"
                            :disabled="!$this->operator_id || !$this->meter_number || $this->amount < 100"
                        >
                            <span>Pay Bill</span>
                            <x-ui.icon name="paper-airplane" class="size-5 ml-2" />
                        </x-ui.button>
                        
                        <p class="text-[10px] text-foreground-content text-center font-medium leading-relaxed">
                            Your token will be displayed on screen and sent via SMS/Email after a successful payment.
                        </p>
                    </div>
                </div>

                <!-- Wallet info quick display -->
                <div class="bg-accent rounded-3xl p-5 text-white shadow-lg overflow-hidden relative group">
                    <div class="relative z-10">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-primary-fg/80 mb-1">Current Balance</p>
                        <p class="text-2xl font-black">{{ Number::currency(auth()->user()->wallet_balance) }}</p>
                    </div>
                    <x-ui.icon name="wallet" class="absolute -right-6 -bottom-6 size-28 text-white/10 group-hover:scale-110 transition-transform" />
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal Refined -->
    <x-ui.modal id="confirm-purchase" heading="Review Electricity Bill">
        <div class="space-y-6">
            <div class="p-5 bg-background rounded-3xl border border-border">
                <div class="flex items-center gap-5 mb-8">
                    <div class="size-20 rounded-2xl bg-background-content p-2 shadow-sm border border-border">
                        <img src="{{ $this->selectedoperator?->image_url }}" alt="" class="size-full object-cover rounded-xl">
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-foreground-content uppercase tracking-widest mb-1">Utility Provider</p>
                        <h4 class="text-2xl font-black text-foreground leading-tight mb-1">{{ $this->selectedoperator?->name }}</h4>
                        <div class="flex gap-2">
                           <span class="px-2 py-1 rounded-lg bg-background text-foreground-content text-[10px] font-black uppercase">{{ $this->meter_type }}</span>
                           <span class="px-2 py-1 rounded-lg bg-primary/10 text-primary text-[10px] font-black uppercase">Instant Token</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-y-6">
                    <div class="space-y-1">
                        <p class="text-[10px] font-bold text-foreground-content uppercase tracking-widest">Meter Number</p>
                        <p class="text-xl font-black tracking-widest text-foreground">{{ $this->meter_number }}</p>
                    </div>
                    <div class="space-y-1 text-right">
                        <p class="text-[10px] font-bold text-foreground-content uppercase tracking-widest">Transaction Fee</p>
                        <p class="text-xl font-black text-success uppercase transition-all">₹0.00</p>
                    </div>
                    <div class="col-span-2 pt-6 border-t border-border flex items-center justify-between">
                        <p class="text-sm font-black text-foreground-content uppercase">Total to Pay</p>
                        <p class="text-3xl font-black text-primary">{{ Number::currency($this->amount) }}</p>
                    </div>
                </div>
            </div>

            <x-ui.alerts type="warning" class="rounded-2xl text-[11px] leading-relaxed">
                Tokens are generated automatically. Please ensure your meter number <strong>{{ $this->meter_number }}</strong> is accurate before proceeding.
            </x-ui.alerts>
        </div>
        
        <x-slot name="footer">
            <div class="flex gap-4 w-full">
                <x-ui.button x-on:click="$data.close();" variant="outline" class="flex-1 h-14 rounded-2xl font-black uppercase tracking-widest text-xs">
                    Go Back
                </x-ui.button>
                <x-ui.button x-on:click="$wire.confirmation()" variant="primary" class="flex-1 h-14 rounded-2xl font-black uppercase tracking-widest text-xs shadow-xl shadow-primary/20">
                    Confirm & Pay
                </x-ui.button>
            </div>
        </x-slot>
    </x-ui.modal>
</div>