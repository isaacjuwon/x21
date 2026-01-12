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
                    <h3 class="text-sm font-bold uppercase tracking-wider text-foreground">1. Select Operator</h3>
                    @if($this->operator_id)
                        <span class="text-xs text-primary font-medium flex items-center">
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
                                'relative flex flex-col items-center p-6 rounded-2xl border-2 transition-all group',
                                'border-primary bg-primary/5 ring-4 ring-primary/10' => $this->operator_id == $operator->id,
                                'border-border bg-background-content hover:border-primary/50' => $this->operator_id != $operator->id
                            ])
                        >
                            <div class="size-16 rounded-xl overflow-hidden mb-3 group-hover:scale-110 transition-transform">
                                <img src="{{ $operator->image_url }}" alt="{{ $operator->name }}" class="size-full object-cover">
                            </div>
                            <span @class([
                                'text-sm font-black uppercase tracking-tight',
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

            <!-- Step 2: Plan Selection -->
            <section @class(['space-y-4 transition-all duration-500', 'opacity-50 pointer-events-none' => !$this->operator_id])>
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-foreground">2. Choose Package</h3>
                </div>

                <div class="grid grid-cols-1 gap-3">
                    @forelse ($this->plans as $p)
                        <button 
                            type="button"
                            wire:click="$set('plan_id', {{ $p->id }})"
                            @class([
                                'flex items-center justify-between p-5 rounded-2xl border-2 text-left transition-all',
                                'border-primary bg-primary/5 ring-2 ring-primary/5' => $this->plan_id == $p->id,
                                'border-border bg-background-content hover:border-primary/50' => $this->plan_id != $p->id
                            ])
                        >
                            <div class="flex items-center gap-4">
                                <div @class([
                                    'size-10 rounded-full flex items-center justify-center',
                                    'bg-primary text-white' => $this->plan_id == $p->id,
                                    'bg-background text-foreground-content' => $this->plan_id != $p->id
                                ])>
                                    <x-ui.icon name="tv" class="size-5" />
                                </div>
                                <div class="space-y-0.5">
                                    <p @class([
                                        'text-base font-black',
                                        'text-primary' => $this->plan_id == $p->id,
                                        'text-foreground' => $this->plan_id != $p->id
                                    ])>{{ $p->name }}</p>
                                    <p class="text-xs text-foreground-content font-medium">Standard Validity • Instant Activation</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span @class([
                                    'text-lg font-black',
                                    'text-primary' => $this->plan_id == $p->id,
                                    'text-foreground' => $this->plan_id != $p->id
                                ])>{{ Number::currency($p->price) }}</span>
                            </div>
                        </button>
                    @empty
                        <div class="py-12 text-center bg-background rounded-3xl border-2 border-dashed border-border">
                            <x-ui.icon name="stop" class="size-10 mx-auto text-foreground-content/30 mb-3" />
                            <p class="text-sm text-foreground-content font-medium">Select an operator to see available packages</p>
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
                    <h3 class="text-sm font-bold uppercase tracking-wider text-foreground">3. IUC / Smartcard Number</h3>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-foreground-content">
                        <x-ui.icon name="credit-card" class="size-6" />
                    </div>
                    <input 
                        type="text" 
                        wire:model.live="smartcard_number"
                        placeholder="Enter your account number" 
                        @class([
                            'w-full pl-14 pr-4 py-5 bg-background-content border-2 rounded-2xl focus:ring-4 focus:ring-primary/10 transition-all text-xl font-bold tracking-widest placeholder:text-foreground-content/50',
                            'border-border focus:border-primary' => !$errors->has('smartcard_number'),
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

        <!-- Sidebar Summary -->
        <div class="lg:col-span-1">
            <div class="sticky top-24 space-y-6">
                <div class="bg-background-content rounded-3xl shadow-xl overflow-hidden border border-border">
                    <div class="p-6 bg-primary border-b border-primary-fg/10">
                        <h4 class="text-primary-fg font-bold uppercase tracking-widest text-xs">Subscription Summary</h4>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        @if($this->selectedOperator)
                            <div class="flex items-center gap-4">
                                <img src="{{ $this->selectedOperator->image_url }}" alt="" class="size-14 rounded-2xl object-cover shadow-md">
                                <div>
                                    <p class="text-xs text-foreground-content font-bold uppercase tracking-wider">Operator</p>
                                    <p class="font-black text-foreground uppercase text-lg">{{ $this->selectedOperator->name }}</p>
                                </div>
                            </div>
                        @endif

                        @if($this->plan)
                            <div class="space-y-1">
                                <p class="text-xs text-foreground-content font-bold uppercase tracking-wider">Selected Package</p>
                                <p class="font-black text-foreground text-xl leading-tight">{{ $this->plan->name }}</p>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 bg-secondary/10 rounded text-[10px] font-bold text-secondary uppercase tracking-tighter">Cable TV</span>
                                </div>
                            </div>

                            <div class="pt-6 border-t border-border">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-foreground-content font-bold text-xs uppercase">Renewal Fee</span>
                                    <span class="text-2xl font-black text-primary">{{ Number::currency($this->plan->price) }}</span>
                                </div>
                            </div>
                        @else
                            <div class="py-12 text-center">
                                <div class="size-16 bg-background rounded-2xl flex items-center justify-center mx-auto mb-4 border-2 border-dashed border-border">
                                    <x-ui.icon name="ticket" class="size-8 text-foreground-content/30" />
                                </div>
                                <p class="text-xs text-foreground-content font-medium max-w-[150px] mx-auto">Select a plan to review your subscription</p>
                            </div>
                        @endif

                        <x-ui.button 
                            wire:click="save"
                            variant="primary" 
                            class="w-full h-14 rounded-2xl font-black uppercase tracking-widest text-sm shadow-lg shadow-primary/20 hover:shadow-primary/40 disabled:opacity-50 disabled:grayscale transition-all"
                            :disabled="!$this->plan_id || !$this->smartcard_number"
                        >
                            <span>Renew Now</span>
                            <x-ui.icon name="chevron-double-right" class="size-5 ml-2" />
                        </x-ui.button>
                        
                        <p class="text-[10px] text-foreground-content text-center font-medium leading-relaxed">
                            Processing typically takes 1-5 minutes. Ensure your decoder is powered on during renewal.
                        </p>
                    </div>
                </div>

                <!-- Wallet info quick display -->
                <div class="bg-accent rounded-3xl p-5 text-white shadow-lg overflow-hidden relative group">
                    <div class="relative z-10">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-primary-fg/80 mb-1">Total Balance</p>
                        <p class="text-2xl font-black">{{ Number::currency(auth()->user()->wallet_balance) }}</p>
                    </div>
                    <x-ui.icon name="wallet" class="absolute -right-6 -bottom-6 size-28 text-white/10 group-hover:scale-110 transition-transform" />
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal Refined -->
    <x-ui.modal id="confirm-purchase" heading="Subscription Review">
        <div class="space-y-6">
            <div class="p-5 bg-background rounded-3xl border border-border">
                <div class="flex items-center gap-5 mb-8">
                    <div class="size-20 rounded-2xl bg-background-content p-2 shadow-sm border border-border">
                        <img src="{{ $this->selectedOperator?->image_url }}" alt="" class="size-full object-cover rounded-xl">
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-foreground-content uppercase tracking-widest mb-1">Selected Operator</p>
                        <h4 class="text-2xl font-black text-foreground leading-tight mb-1">{{ $this->selectedOperator?->name }}</h4>
                        <span class="px-2 py-1 rounded-lg bg-primary/10 text-primary text-[10px] font-black uppercase">{{ $this->plan?->name }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-y-6">
                    <div class="space-y-1">
                        <p class="text-[10px] font-bold text-foreground-content uppercase tracking-widest">Account / IUC</p>
                        <p class="text-xl font-black tracking-widest text-foreground">{{ $this->smartcard_number }}</p>
                    </div>
                    <div class="space-y-1 text-right">
                        <p class="text-[10px] font-bold text-foreground-content uppercase tracking-widest">Type</p>
                        <p class="text-xl font-black text-foreground uppercase transition-all">RENEWAL</p>
                    </div>
                    <div class="col-span-2 pt-6 border-t border-border flex items-center justify-between">
                        <p class="text-sm font-black text-foreground-content uppercase">Amount Due</p>
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
