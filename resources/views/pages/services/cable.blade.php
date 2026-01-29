<?php

use App\Actions\GenerateReferenceAction;
use App\Actions\Services\CablePurchaseAction;
use App\Livewire\Concerns\HasToast;
use App\Livewire\Concerns\WithConfirmation;
use App\Models\Brand;
use App\Models\CablePlan;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;

new class extends Component
{
    use HasToast, WithConfirmation;

    #[Rule('required')]
    public string|int $operator_id = '';

    #[Rule('required')]
    public string|int $plan_id = '';

    #[Rule('required|min:10|max:15')]
    public $smartcard_number;

    public $plan;

    #[On('form-confirmed-purchase')]
    public function save(CablePurchaseAction $cablePurchaseAction, GenerateReferenceAction $generateReferenceAction)
    {
        $this->validate();

        if (! $this->ensureConfirmation('purchase')) {
            return;
        }

        $data = [
            'operator_name' => $this->selectedOperator->name,
            'smartcard_number' => $this->smartcard_number,
            'plan_code' => $this->plan->vcode ?? $this->plan->name,
            'reference' => $generateReferenceAction->handle('CABLE'),
            'plan_id' => $this->plan->id,
            'amount' => $this->plan->price,
        ];

        $result = $cablePurchaseAction->handle($data);

        if ($result->isError()) {
            $this->toastError($result->error->getMessage());

            return;
        }

        // If we get here, the result is OK
        $responseData = $result->unwrap();
        $this->toastSuccess($responseData['message'] ?? 'Cable subscription successful.');

        // Reset form fields
        $this->reset(['operator_id', 'plan_id', 'smartcard_number']);
    }

    #[Computed]
    public function selectedOperator()
    {
        return $this->operators->firstWhere('id', $this->operator_id);
    }

    #[Computed]
    public function operators()
    {
        return Brand::active()
            ->whereHas('cablePlans', function ($query) {
                $query->where('status', true);
            })
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function plans()
    {
        if (! $this->operator_id) {
            return collect();
        }

        return CablePlan::where('brand_id', $this->operator_id)
            ->where('status', true)
            ->orderBy('name')
            ->get();
    }

    public function updated($property, $value): void
    {
        if ($property === 'operator_id') {
            $this->plan_id = '';
            $this->plan = null;
        }

        if ($property === 'plan_id') {
            $this->plan = CablePlan::find($value);
        }
    }

    public function render()
    {
        return $this->view()
            ->title('Cable Subscription')
            ->layout('layouts::app');
    }
}; ?>


<div class="max-w-4xl mx-auto p-6">
    <x-page-header 
        heading="Cable TV" 
        description="Renew your cable subscription or upgrade your plan instantly"
    />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Form Content -->
        <div class="lg:col-span-2">
            <div data-slot="card" class="p-6 bg-background-content rounded-3xl border border-border space-y-8">
                <!-- Step 1: Operator Selection -->
                <section class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[10px] font-bold uppercase tracking-widest text-neutral-500 dark:text-neutral-400">1. Select Operator</h3>
                        @if($this->operator_id)
                            <span class="text-[10px] text-primary font-bold flex items-center uppercase tracking-widest">
                                <x-ui.icon name="check-circle" class="size-4 mr-1" />
                                {{ $this->selectedOperator?->name }}
                            </span>
                        @endif
                    </div>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        @foreach ($this->operators as $operator)
                            <button 
                                type="button"
                                wire:click="$set('operator_id', {{ $operator->id }})"
                                @class([
                                    'relative flex flex-col items-center p-6 rounded-[--radius-box] border-2 transition-all group',
                                    'border-primary bg-primary/5 ring-4 ring-primary/10' => $this->operator_id == $operator->id,
                                    'border-neutral-100 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-900/50 hover:border-primary/50' => $this->operator_id != $operator->id
                                ])
                            >
                                <div class="size-16 rounded-[--radius-field] overflow-hidden mb-3 group-hover:scale-110 transition-transform">
                                    <img src="{{ $operator->image_url }}" alt="{{ $operator->name }}" class="size-full object-cover">
                                </div>
                                <span @class([
                                    'text-[10px] font-bold uppercase tracking-widest',
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

                <!-- Step 2: Plan Selection -->
                <section @class(['space-y-4 transition-all duration-500', 'opacity-50 pointer-events-none' => !$this->operator_id])>
                    <div class="flex items-center justify-between">
                        <h3 class="text-[10px] font-bold uppercase tracking-widest text-neutral-500 dark:text-neutral-400">2. Choose Package</h3>
                    </div>
 
                    <div class="grid grid-cols-1 gap-3">
                        @forelse ($this->plans as $p)
                            <button 
                                type="button"
                                wire:click="$set('plan_id', {{ $p->id }})"
                                @class([
                                    'flex items-center justify-between p-5 rounded-[--radius-box] border-2 text-left transition-all',
                                    'border-primary bg-primary/5 ring-2 ring-primary/5' => $this->plan_id == $p->id,
                                    'border-neutral-100 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-900/50 hover:border-primary/50' => $this->plan_id != $p->id
                                ])
                            >
                                <div class="flex items-center gap-4">
                                    <div @class([
                                        'size-10 rounded-full flex items-center justify-center',
                                        'bg-primary text-white' => $this->plan_id == $p->id,
                                        'bg-neutral-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400' => $this->plan_id != $p->id
                                    ])>
                                        <x-ui.icon name="tv" class="size-5" />
                                    </div>
                                    <div class="space-y-0.5">
                                        <p @class([
                                            'text-base font-bold',
                                            'text-primary' => $this->plan_id == $p->id,
                                            'text-neutral-900 dark:text-white' => $this->plan_id != $p->id
                                        ])>{{ $p->name }}</p>
                                        <p class="text-[10px] text-neutral-500 dark:text-neutral-400 font-bold uppercase tracking-widest">Standard Validity • Instant Activation</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span @class([
                                        'text-lg font-bold',
                                        'text-primary' => $this->plan_id == $p->id,
                                        'text-neutral-900 dark:text-white' => $this->plan_id != $p->id
                                    ])>{{ Number::currency($p->price) }}</span>
                                </div>
                            </button>
                        @empty
                            <div class="py-12 text-center bg-neutral-50 dark:bg-neutral-900/50 rounded-[--radius-box] border-2 border-dashed border-neutral-100 dark:border-neutral-700">
                                <x-ui.icon name="stop" class="size-10 mx-auto text-neutral-300 dark:text-neutral-500 mb-3" />
                                <p class="text-[10px] text-neutral-500 dark:text-neutral-400 font-bold uppercase tracking-widest">Select an operator to see packages</p>
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

                <!-- Step 3: Smartcard Number -->
                <section @class(['space-y-4 transition-all duration-500', 'opacity-50 pointer-events-none' => !$this->plan_id])>
                    <div class="flex items-center justify-between">
                        <h3 class="text-[10px] font-bold uppercase tracking-widest text-neutral-500 dark:text-neutral-400">3. IUC / Smartcard Number</h3>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-neutral-400">
                            <x-ui.icon name="credit-card" class="size-6" />
                        </div>
                        <input 
                            type="text" 
                            wire:model.live.debounce.50ms="smartcard_number"
                            placeholder="Enter your account number" 
                            @class([
                                'w-full pl-14 pr-4 py-5 bg-neutral-50 dark:bg-neutral-900/50 border-2 rounded-[--radius-box] focus:ring-4 focus:ring-primary/10 transition-all text-xl font-bold tracking-widest placeholder:text-neutral-500/50',
                                'border-neutral-100 dark:border-neutral-700 focus:border-primary' => !$errors->has('smartcard_number'),
                                'border-error focus:border-error' => $errors->has('smartcard_number'),
                            ])
                        >
                    </div>
                    @error('smartcard_number')
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
                        <h4 class="text-white font-bold uppercase tracking-widest text-[10px]">Subscription Summary</h4>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        @if($this->selectedOperator)
                            <div class="flex items-center gap-4">
                                <img src="{{ $this->selectedOperator->image_url }}" alt="" class="size-14 rounded-[--radius-field] object-cover shadow-md">
                                <div>
                                    <p class="text-[10px] text-neutral-500 dark:text-neutral-400 font-bold uppercase tracking-widest">Operator</p>
                                    <p class="font-bold text-neutral-900 dark:text-white uppercase text-base">{{ $this->selectedOperator->name }}</p>
                                </div>
                            </div>
                        @endif

                        @if($this->plan)
                            <div class="space-y-1">
                                <p class="text-[10px] text-neutral-500 dark:text-neutral-400 font-bold uppercase tracking-widest">Selected Package</p>
                                <p class="font-bold text-neutral-900 dark:text-white text-base leading-tight">{{ $this->plan->name }}</p>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 bg-secondary/10 rounded text-[10px] font-bold text-secondary uppercase tracking-widest">Cable TV</span>
                                </div>
                            </div>

                            <div class="pt-6 border-t border-neutral-100 dark:border-neutral-700">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-neutral-500 dark:text-neutral-400 font-bold text-[10px] uppercase tracking-widest">Renewal Fee</span>
                                    <span class="text-xl font-bold text-primary">{{ Number::currency($this->plan->price) }}</span>
                                </div>
                            </div>
                        @else
                            <div class="py-12 text-center">
                                <div class="size-16 bg-neutral-50 dark:bg-neutral-900/50 rounded-[--radius-box] flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-neutral-100 dark:border-neutral-700">
                                    <x-ui.icon name="ticket" class="size-8 text-neutral-300 dark:text-neutral-500" />
                                </div>
                                <p class="text-[10px] text-neutral-500 dark:text-neutral-400 font-bold max-w-[150px] mx-auto uppercase tracking-widest">Select a plan to review subscription</p>
                            </div>
                        @endif

                        <x-ui.button 
                            wire:click="save"
                            variant="primary" 
                            icon="chevron-double-right"
                            class="w-full h-14 rounded-[--radius-box] font-bold uppercase tracking-widest text-[10px] shadow-lg shadow-primary/20 hover:shadow-primary/40 disabled:opacity-50 disabled:grayscale transition-all"
                            :disabled="!$this->plan_id || !$this->smartcard_number"
                        >
                            <span>Renew Now</span>
                        </x-ui.button>
                        
                        <p class="text-[10px] text-neutral-500 dark:text-neutral-400 text-center font-bold uppercase tracking-widest leading-relaxed">
                            Processing typically takes 1-5 minutes. Ensure your decoder is powered on during renewal.
                        </p>
                    </div>
                </div>

                <!-- Wallet info quick display -->
                <div class="bg-accent rounded-[--radius-box] p-5 text-white shadow-lg overflow-hidden relative group">
                    <div class="relative z-10">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-white/80 mb-1">Total Balance</p>
                        <p class="text-2xl font-bold">{{ Number::currency(auth()->user()->wallet_balance) }}</p>
                    </div>
                    <x-ui.icon name="wallet" class="absolute -right-6 -bottom-6 size-28 text-white/10 group-hover:scale-110 transition-transform" />
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal Refined -->
    <x-ui.modal id="confirm-purchase" heading="Subscription Review">
        <div class="space-y-6">
            <div class="p-5 bg-neutral-50 dark:bg-neutral-900/50 rounded-[--radius-box] border border-neutral-100 dark:border-neutral-700">
                <div class="flex items-center gap-5 mb-8">
                    <div class="size-20 rounded-[--radius-field] bg-white dark:bg-neutral-800 p-2 shadow-sm border border-neutral-100 dark:border-neutral-700">
                        <img src="{{ $this->selectedOperator?->image_url }}" alt="" class="size-full object-cover rounded-[--radius-field]">
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mb-1">Selected Operator</p>
                        <h4 class="text-xl font-bold text-neutral-900 dark:text-white leading-tight mb-1">{{ $this->selectedOperator?->name }}</h4>
                        <span class="px-2 py-1 rounded-[--radius-field] bg-primary/10 text-primary text-[10px] font-bold uppercase tracking-widest">{{ $this->plan?->name }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-y-6">
                    <div class="space-y-1">
                        <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest">Account / IUC</p>
                        <p class="text-lg font-bold tracking-widest text-neutral-900 dark:text-white">{{ $this->smartcard_number }}</p>
                    </div>
                    <div class="space-y-1 text-right">
                        <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest">Type</p>
                        <p class="text-lg font-bold text-neutral-900 dark:text-white uppercase tracking-widest transition-all">RENEWAL</p>
                    </div>
                    <div class="col-span-2 pt-6 border-t border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
                        <p class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest">Amount Due</p>
                        <p class="text-2xl font-bold text-primary">{{ Number::currency($this->plan?->price ?? 0) }}</p>
                    </div>
                </div>
            </div>

            <x-ui.alerts type="info" class="rounded-2xl text-[11px] leading-relaxed">
                Confirming this will immediately debit your wallet and trigger the update for the provided IUC number.
            </x-ui.alerts>
        </div>
        
        <x-slot name="footer">
            <div class="flex gap-4 w-full">
                <x-ui.button x-on:click="$data.close();" variant="outline" class="flex-1 h-14 rounded-[--radius-field] font-bold uppercase tracking-widest text-[10px]">
                    Cancel
                </x-ui.button>
                <x-ui.button x-on:click="$wire.confirmation()" variant="primary" class="flex-1 h-14 rounded-[--radius-field] font-bold uppercase tracking-widest text-[10px] shadow-xl shadow-primary/20">
                    Confirm Payment
                </x-ui.button>
            </div>
        </x-slot>
    </x-ui.modal>
</div>
