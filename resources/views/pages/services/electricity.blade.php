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
        <div class="lg:col-span-2">
            <div data-slot="card" class="p-6 bg-background-content rounded-3xl border border-border space-y-8">
                <!-- Step 1: Operator Selection -->
                <section class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[10px] font-bold uppercase tracking-widest text-neutral-500 dark:text-neutral-400">1. Select Distributor</h3>
                        @if($this->operator_id)
                            <span class="text-[10px] text-primary font-bold flex items-center uppercase tracking-widest">
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
                                    'relative flex flex-col items-center p-5 rounded-[--radius-box] border-2 transition-all group',
                                    'border-primary bg-primary/5 ring-4 ring-primary/10' => $this->operator_id == $operator->id,
                                    'border-neutral-100 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-900/50 hover:border-primary/50' => $this->operator_id != $operator->id
                                ])
                            >
                                <div class="size-14 rounded-[--radius-field] overflow-hidden mb-3 group-hover:scale-110 transition-transform">
                                    <img src="{{ $operator->image_url }}" alt="{{ $operator->name }}" class="size-full object-cover">
                                </div>
                                <span @class([
                                    'text-[10px] font-bold uppercase tracking-widest text-center leading-tight',
                                    'text-primary' => $this->operator_id == $operator->id,
                                    'text-neutral-500 dark:text-neutral-400' => $this->operator_id != $operator->id
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
                        <h3 class="text-[10px] font-bold uppercase tracking-widest text-neutral-500 dark:text-neutral-400">2. Meter Type</h3>
                        @if($this->meter_type)
                            <span class="text-[10px] text-primary font-bold uppercase tracking-widest">{{ $this->meter_type }}</span>
                        @endif
                    </div>

                    <div class="flex gap-3">
                        @foreach(['prepaid', 'postpaid'] as $type)
                            <button 
                                type="button" 
                                wire:click="$set('meter_type', '{{ $type }}')"
                                @class([
                                    'flex-1 py-4 rounded-[--radius-box] border-2 font-bold uppercase tracking-widest text-[10px] transition-all',
                                    'border-primary bg-primary text-white shadow-lg shadow-primary/20' => $this->meter_type == $type,
                                    'border-neutral-100 dark:border-neutral-700 text-neutral-500 dark:text-neutral-400 bg-neutral-50 dark:bg-neutral-900/50 hover:border-primary/30' => $this->meter_type != $type
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
                    <h3 class="text-[10px] font-bold uppercase tracking-widest text-neutral-500 dark:text-neutral-400">3. Meter Number</h3>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-neutral-400">
                            <x-ui.icon name="hashtag" class="size-6" />
                        </div>
                        <input 
                            type="text" 
                            wire:model.live.debounce.50ms="meter_number"
                            placeholder="Enter your meter number" 
                            @class([
                                'w-full pl-14 pr-4 py-5 bg-neutral-50 dark:bg-neutral-900/50 border-2 rounded-[--radius-box] focus:ring-4 focus:ring-primary/10 transition-all text-base font-bold tracking-widest placeholder:text-neutral-500/50',
                                'border-neutral-100 dark:border-neutral-700 focus:border-primary' => !$errors->has('meter_number'),
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
                    <h3 class="text-[10px] font-bold uppercase tracking-widest text-neutral-500 dark:text-neutral-400">4. Amount</h3>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-primary font-bold text-xl">
                            ₦
                        </div>
                        <input 
                            type="number" 
                            wire:model.live.debounce.50ms="amount"
                            placeholder="0.00" 
                            @class([
                                'w-full pl-12 pr-4 py-5 bg-neutral-50 dark:bg-neutral-900/50 border-2 rounded-[--radius-box] focus:ring-4 focus:ring-primary/10 transition-all text-xl font-bold placeholder:text-neutral-500/30',
                                'border-neutral-100 dark:border-neutral-700 focus:border-primary' => !$errors->has('amount'),
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
        </div>

        <!-- Sidebar Summary -->
        <div class="lg:col-span-1">
            <div class="sticky top-24 space-y-6">
                <div class="bg-white dark:bg-neutral-800 rounded-[--radius-box] shadow-xl overflow-hidden border border-neutral-100 dark:border-neutral-700">
                    <div class="p-6 bg-primary border-b border-white/10">
                        <h4 class="text-white font-bold uppercase tracking-widest text-[10px]">Payment Summary</h4>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        @if($this->selectedoperator)
                            <div class="flex items-center gap-4">
                                <img src="{{ $this->selectedoperator->image_url }}" alt="" class="size-14 rounded-[--radius-field] object-cover shadow-md">
                                <div>
                                    <p class="text-[10px] text-neutral-500 dark:text-neutral-400 font-bold uppercase tracking-widest">Distributor</p>
                                    <p class="font-bold text-neutral-900 dark:text-white uppercase text-base leading-tight">{{ $this->selectedoperator->name }}</p>
                                </div>
                            </div>
                        @endif

                        @if($this->amount > 0)
                            <div class="space-y-1">
                                <p class="text-[10px] text-neutral-500 dark:text-neutral-400 font-bold uppercase tracking-widest">Total Charge</p>
                                <p class="font-bold text-neutral-900 dark:text-white text-xl leading-tight">{{ Number::currency($this->amount) }}</p>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 bg-warning/10 rounded text-[10px] font-bold text-warning uppercase tracking-widest">{{ $this->meter_type ?? 'Meter' }}</span>
                                    <span class="text-[10px] text-neutral-500 dark:text-neutral-400 font-bold uppercase tracking-widest">Token instant delivery</span>
                                </div>
                            </div>
                        @else
                            <div class="py-12 text-center">
                                <div class="size-16 bg-neutral-50 dark:bg-neutral-900/50 rounded-[--radius-box] flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-neutral-100 dark:border-neutral-700">
                                    <x-ui.icon name="light-bulb" class="size-8 text-neutral-300 dark:text-neutral-500" />
                                </div>
                                <p class="text-[10px] text-neutral-500 dark:text-neutral-400 font-bold max-w-[150px] mx-auto uppercase tracking-widest">Complete the form to see summary</p>
                            </div>
                        @endif

                        <x-ui.button 
                            wire:click="save"
                            variant="primary" 
                            icon="paper-airplane" 
                            class="w-full h-14 rounded-[--radius-box] font-bold uppercase tracking-widest text-[10px] shadow-lg shadow-primary/20 hover:shadow-primary/40 disabled:opacity-50 disabled:grayscale transition-all"
                            :disabled="!$this->operator_id || !$this->meter_number || $this->amount < 100"
                        >
                            <span>Pay Bill</span>
                        </x-ui.button>
                        
                        <p class="text-[10px] text-neutral-500 dark:text-neutral-400 text-center font-bold uppercase tracking-widest leading-relaxed">
                            Your token will be displayed on screen and sent via SMS/Email after a successful payment.
                        </p>
                    </div>
                </div>

                <!-- Wallet info quick display -->
                <div class="bg-accent rounded-[--radius-box] p-5 text-white shadow-lg overflow-hidden relative group">
                    <div class="relative z-10">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-white/80 mb-1">Current Balance</p>
                        <p class="text-2xl font-bold">{{ Number::currency(auth()->user()->wallet_balance) }}</p>
                    </div>
                    <x-ui.icon name="wallet" class="absolute -right-6 -bottom-6 size-28 text-white/10 group-hover:scale-110 transition-transform" />
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal Refined -->
    <x-ui.modal id="confirm-purchase" heading="Review Electricity Bill">
        <div class="space-y-6">
            <div class="p-5 bg-neutral-50 dark:bg-neutral-900/50 rounded-[--radius-box] border border-neutral-100 dark:border-neutral-700">
                <div class="flex items-center gap-5 mb-8">
                    <div class="size-20 rounded-[--radius-field] bg-white dark:bg-neutral-800 p-2 shadow-sm border border-neutral-100 dark:border-neutral-700">
                        <img src="{{ $this->selectedoperator?->image_url }}" alt="" class="size-full object-cover rounded-[--radius-field]">
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mb-1">Utility Provider</p>
                        <h4 class="text-xl font-bold text-neutral-900 dark:text-white leading-tight mb-1">{{ $this->selectedoperator?->name }}</h4>
                        <div class="flex gap-2">
                           <span class="px-2 py-1 rounded-[--radius-field] bg-neutral-100 dark:bg-neutral-900/50 text-neutral-500 dark:text-neutral-400 text-[10px] font-bold uppercase tracking-widest">{{ $this->meter_type }}</span>
                           <span class="px-2 py-1 rounded-[--radius-field] bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-widest">Instant Token</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-y-6">
                    <div class="space-y-1">
                        <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest">Meter Number</p>
                        <p class="text-lg font-bold tracking-widest text-neutral-900 dark:text-white">{{ $this->meter_number }}</p>
                    </div>
                    <div class="space-y-1 text-right">
                        <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest">Transaction Fee</p>
                        <p class="text-lg font-bold text-success uppercase tracking-widest transition-all">FREE</p>
                    </div>
                    <div class="col-span-2 pt-6 border-t border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
                        <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest">Total to Pay</p>
                        <p class="text-2xl font-bold text-primary">{{ Number::currency($this->amount) }}</p>
                    </div>
                </div>
            </div>

            <x-ui.alerts type="warning" class="rounded-2xl text-[11px] leading-relaxed">
                Tokens are generated automatically. Please ensure your meter number <strong>{{ $this->meter_number }}</strong> is accurate before proceeding.
            </x-ui.alerts>
        </div>
        
        <x-slot name="footer">
            <div class="flex gap-4 w-full">
                <x-ui.button x-on:click="$data.close();" variant="outline" class="flex-1 h-14 rounded-[--radius-field] font-bold uppercase tracking-widest text-[10px]">
                    Go Back
                </x-ui.button>
                <x-ui.button x-on:click="$wire.confirmation()" variant="primary" class="flex-1 h-14 rounded-[--radius-field] font-bold uppercase tracking-widest text-[10px] shadow-xl shadow-primary/20">
                    Confirm & Pay
                </x-ui.button>
            </div>
        </x-slot>
    </x-ui.modal>
</div>