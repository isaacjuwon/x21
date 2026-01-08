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
        <div class="lg:col-span-2 space-y-8">
            <!-- Step 1: Operator Selection -->
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-zinc-500">1. Select Operator</h3>
                    @if($this->operator_id)
                        <span class="text-xs text-primary font-medium flex items-center">
                            <x-ui.icon name="check-circle" class="w-4 h-4 mr-1" />
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
                                'relative flex flex-col items-center p-6 rounded-2xl border-2 transition-all group',
                                'border-primary bg-primary/5 ring-4 ring-primary/10' => $this->operator_id == $operator->id,
                                'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:border-primary/50' => $this->operator_id != $operator->id
                            ])
                        >
                            <div class="w-16 h-16 rounded-xl overflow-hidden mb-3 group-hover:scale-110 transition-transform">
                                <img src="{{ $operator->image_url }}" alt="{{ $operator->name }}" class="w-full h-full object-cover">
                            </div>
                            <span @class([
                                'text-sm font-black uppercase tracking-tight',
                                'text-primary' => $this->operator_id == $operator->id,
                                'text-zinc-600 dark:text-zinc-400' => $this->operator_id != $operator->id
                            ])>{{ $operator->name }}</span>
                            
                            @if($this->operator_id == $operator->id)
                                <div class="absolute -top-2 -right-2 w-6 h-6 bg-primary text-white rounded-full flex items-center justify-center shadow-lg">
                                    <x-ui.icon name="check" class="w-4 h-4" />
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
                    <h3 class="text-sm font-bold uppercase tracking-wider text-zinc-500">2. Choose Package</h3>
                </div>

                <div class="grid grid-cols-1 gap-3">
                    @forelse ($this->plans as $p)
                        <button 
                            type="button"
                            wire:click="$set('plan_id', {{ $p->id }})"
                            @class([
                                'flex items-center justify-between p-5 rounded-2xl border-2 text-left transition-all',
                                'border-primary bg-primary/5 ring-2 ring-primary/5' => $this->plan_id == $p->id,
                                'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:border-primary/50' => $this->plan_id != $p->id
                            ])
                        >
                            <div class="flex items-center gap-4">
                                <div @class([
                                    'w-10 h-10 rounded-full flex items-center justify-center',
                                    'bg-primary text-white' => $this->plan_id == $p->id,
                                    'bg-zinc-100 dark:bg-zinc-900 text-zinc-400' => $this->plan_id != $p->id
                                ])>
                                    <x-ui.icon name="tv" class="w-5 h-5" />
                                </div>
                                <div class="space-y-0.5">
                                    <p @class([
                                        'text-base font-black',
                                        'text-primary' => $this->plan_id == $p->id,
                                        'text-zinc-900 dark:text-white' => $this->plan_id != $p->id
                                    ])>{{ $p->name }}</p>
                                    <p class="text-xs text-zinc-500 font-medium">Standard Validity • Instant Activation</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span @class([
                                    'text-lg font-black',
                                    'text-primary' => $this->plan_id == $p->id,
                                    'text-zinc-900 dark:text-white' => $this->plan_id != $p->id
                                ])>{{ Number::currency($p->price) }}</span>
                            </div>
                        </button>
                    @empty
                        <div class="py-12 text-center bg-zinc-50 dark:bg-zinc-800/50 rounded-3xl border-2 border-dashed border-zinc-200 dark:border-zinc-700">
                            <x-ui.icon name="stop" class="w-10 h-10 mx-auto text-zinc-300 mb-3" />
                            <p class="text-sm text-zinc-500 font-medium">Select an operator to see available packages</p>
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
                    <h3 class="text-sm font-bold uppercase tracking-wider text-zinc-500">3. IUC / Smartcard Number</h3>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-zinc-400">
                        <x-ui.icon name="credit-card" class="w-6 h-6" />
                    </div>
                    <input 
                        type="text" 
                        wire:model.live="smartcard_number"
                        placeholder="Enter your account number" 
                        @class([
                            'w-full pl-14 pr-4 py-5 bg-white dark:bg-zinc-800 border-2 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-xl font-bold tracking-widest placeholder:text-zinc-300 dark:placeholder:text-zinc-600',
                            'border-zinc-200 dark:border-zinc-700 focus:border-primary' => !$errors->has('smartcard_number'),
                            'border-red-500 focus:border-red-500' => $errors->has('smartcard_number'),
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

        <!-- Sidebar Summary -->
        <div class="lg:col-span-1">
            <div class="sticky top-24 space-y-6">
                <div class="bg-white dark:bg-zinc-800 rounded-3xl shadow-xl overflow-hidden border border-zinc-100 dark:border-zinc-700">
                    <div class="p-6 bg-zinc-900 border-b border-zinc-800">
                        <h4 class="text-white font-bold uppercase tracking-widest text-xs">Subscription Summary</h4>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        @if($this->selectedOperator)
                            <div class="flex items-center gap-4">
                                <img src="{{ $this->selectedOperator->image_url }}" alt="" class="w-14 h-14 rounded-2xl object-cover shadow-md">
                                <div>
                                    <p class="text-xs text-zinc-500 font-bold uppercase tracking-wider">Operator</p>
                                    <p class="font-black text-zinc-900 dark:text-white uppercase text-lg">{{ $this->selectedOperator->name }}</p>
                                </div>
                            </div>
                        @endif

                        @if($this->plan)
                            <div class="space-y-1">
                                <p class="text-xs text-zinc-500 font-bold uppercase tracking-wider">Selected Package</p>
                                <p class="font-black text-zinc-900 dark:text-white text-xl leading-tight">{{ $this->plan->name }}</p>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900/40 rounded text-[10px] font-bold text-blue-700 dark:text-blue-400 uppercase tracking-tighter">Cable TV</span>
                                </div>
                            </div>

                            <div class="pt-6 border-t border-zinc-100 dark:border-zinc-700">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-zinc-500 font-bold text-xs uppercase">Renewal Fee</span>
                                    <span class="text-2xl font-black text-primary">{{ Number::currency($this->plan->price) }}</span>
                                </div>
                            </div>
                        @else
                            <div class="py-12 text-center">
                                <div class="w-16 h-16 bg-zinc-50 dark:bg-zinc-900/50 rounded-2xl flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-zinc-200 dark:border-zinc-700">
                                    <x-ui.icon name="ticket" class="w-8 h-8 text-zinc-300" />
                                </div>
                                <p class="text-xs text-zinc-400 font-medium max-w-[150px] mx-auto">Select a plan to review your subscription</p>
                            </div>
                        @endif

                        <x-ui.button 
                            wire:click="save"
                            variant="primary" 
                            class="w-full h-14 rounded-2xl font-black uppercase tracking-widest text-sm shadow-lg shadow-primary/20 hover:shadow-primary/40 disabled:opacity-50 disabled:grayscale transition-all"
                            :disabled="!$this->plan_id || !$this->smartcard_number"
                        >
                            <span>Renew Now</span>
                            <x-ui.icon name="chevron-double-right" class="w-5 h-5 ml-2" />
                        </x-ui.button>
                        
                        <p class="text-[10px] text-zinc-400 text-center font-medium leading-relaxed">
                            Processing typically takes 1-5 minutes. Ensure your decoder is powered on during renewal.
                        </p>
                    </div>
                </div>

                <!-- Wallet info quick display -->
                <div class="bg-indigo-600 rounded-3xl p-5 text-white shadow-lg overflow-hidden relative group">
                    <div class="relative z-10">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-indigo-100 mb-1">Total Balance</p>
                        <p class="text-2xl font-black">{{ Number::currency(auth()->user()->wallet_balance) }}</p>
                    </div>
                    <x-ui.icon name="wallet" class="absolute -right-6 -bottom-6 w-28 h-28 text-white/10 group-hover:scale-110 transition-transform" />
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal Refined -->
    <x-ui.modal id="confirm-purchase" heading="Subscription Review">
        <div class="space-y-6">
            <div class="p-5 bg-zinc-50 dark:bg-zinc-900 rounded-3xl border border-zinc-100 dark:border-zinc-700">
                <div class="flex items-center gap-5 mb-8">
                    <div class="w-20 h-20 rounded-2xl bg-white dark:bg-zinc-800 p-2 shadow-sm border border-zinc-100 dark:border-zinc-700">
                        <img src="{{ $this->selectedOperator?->image_url }}" alt="" class="w-full h-full object-cover rounded-xl">
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest mb-1">Selected Operator</p>
                        <h4 class="text-2xl font-black text-zinc-900 dark:text-white leading-tight mb-1">{{ $this->selectedOperator?->name }}</h4>
                        <span class="px-2 py-1 rounded-lg bg-primary/10 text-primary text-[10px] font-black uppercase">{{ $this->plan?->name }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-y-6">
                    <div class="space-y-1">
                        <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">Account / IUC</p>
                        <p class="text-xl font-black tracking-widest text-zinc-900 dark:text-white">{{ $this->smartcard_number }}</p>
                    </div>
                    <div class="space-y-1 text-right">
                        <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">Type</p>
                        <p class="text-xl font-black text-zinc-900 dark:text-white uppercase transition-all">RENEWAL</p>
                    </div>
                    <div class="col-span-2 pt-6 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                        <p class="text-sm font-black text-zinc-500 uppercase">Amount Due</p>
                        <p class="text-3xl font-black text-primary">{{ Number::currency($this->plan?->price ?? 0) }}</p>
                    </div>
                </div>
            </div>

            <x-ui.alerts type="info" class="rounded-2xl text-[11px] leading-relaxed">
                Confirming this will immediately debit your wallet and trigger the update for the provided IUC number.
            </x-ui.alerts>
        </div>
        
        <x-slot name="footer">
            <div class="flex gap-4 w-full">
                <x-ui.button x-on:click="$data.close();" variant="outline" class="flex-1 h-14 rounded-2xl font-black uppercase tracking-widest text-xs">
                    Cancel
                </x-ui.button>
                <x-ui.button x-on:click="$wire.confirmation()" variant="primary" class="flex-1 h-14 rounded-2xl font-black uppercase tracking-widest text-xs shadow-xl shadow-primary/20">
                    Confirm Payment
                </x-ui.button>
            </div>
        </x-slot>
    </x-ui.modal>
</div>
