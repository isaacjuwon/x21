<?php

use App\Models\Brand;
use App\Models\DataPlan;
use App\Models\TopupTransaction;
use App\Enums\Wallets\WalletType;
use App\Actions\Vtu\PurchaseDataAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;

new #[Title('Data Purchase')] class extends Component {
    public $brand_id;
    public $type_filter;
    public $plan_id;
    public $phone_number;

    protected $rules = [
        'brand_id' => 'required|exists:brands,id',
        'plan_id' => 'required|exists:data_plans,id',
        'phone_number' => 'required|string|min:10',
    ];

    #[Computed]
    public function brands()
    {
        return Brand::whereHas('dataPlans', fn($q) => $q->where('status', true))
            ->where('status', true)
            ->get();
    }

    #[Computed]
    public function types()
    {
        if (!$this->brand_id) return collect();

        return DataPlan::where('brand_id', $this->brand_id)
            ->where('status', true)
            ->whereNotNull('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');
    }

    #[Computed]
    public function plans()
    {
        if (!$this->brand_id || !$this->type_filter) return collect();

        return DataPlan::where('brand_id', $this->brand_id)
            ->where('type', $this->type_filter)
            ->where('status', true)
            ->orderBy('price')
            ->get();
    }

    #[Computed]
    public function selectedPlan()
    {
        if (!$this->plan_id) return null;
        return DataPlan::find($this->plan_id);
    }

    public function updatedBrandId()
    {
        $this->reset('type_filter', 'plan_id');
    }

    public function updatedTypeFilter()
    {
        $this->reset('plan_id');
    }

    public function buy(PurchaseDataAction $purchaseAction)
    {
        $this->validate();

        $user = Auth::user();
        $plan = $this->selectedPlan;

        if ($user->wallet->available_balance < $plan->price) {
            $this->addError('plan_id', 'Insufficient wallet balance.');
            return;
        }

        try {
            $transaction = DB::transaction(function () use ($user, $plan) {
                $topup = TopupTransaction::create([
                    'user_id' => $user->id,
                    'brand_id' => $plan->brand_id,
                    'plan_id' => $plan->id,
                    'plan_type' => DataPlan::class,
                    'type' => \App\Enums\Topups\TopupType::Data,
                    'amount' => $plan->price,
                    'recipient' => $this->phone_number,
                    'status' => 'pending',
                    'reference' => 'DAT-'.strtoupper(Str::random(10)),
                ]);

                $user->withdraw($plan->price, WalletType::General, "Data Purchase: {$plan->brand->name} {$plan->type} ({$this->phone_number})", $topup);

                return $topup;
            });

            $purchaseAction->handle($transaction);

            Flux::toast('Data purchase initiated successfully.');
            $this->reset(['plan_id', 'phone_number', 'brand_id', 'type_filter']);
        } catch (\Exception $e) {
            if (app()->isProduction()) {
                Flux::toast('An error occurred during the transaction. Please try again later.', variant: 'danger');
            } else {
                $this->addError('plan_id', 'An error occurred during the transaction: ' . $e->getMessage());
            }
        }
    }
}; ?>

<div class="max-w-2xl mx-auto space-y-6">
    <flux:heading size="xl">Data Bundle Purchase</flux:heading>
    <flux:subheading>Stay connected with cheap data bundles.</flux:subheading>

    <flux:card>
        <form wire:submit="buy" class="space-y-6">
            <!-- Brand Selection -->
            <flux:field>
                <flux:label>Select Network Provider</flux:label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach($this->brands as $brand)
                        <label class="relative cursor-pointer group">
                            <input type="radio" wire:model.live="brand_id" value="{{ $brand->id }}" class="sr-only peer">
                            <div class="p-4 border-2 rounded-xl flex flex-col items-center gap-2 transition-all peer-checked:border-primary-color peer-checked:bg-primary-color/5 peer-checked:shadow-md peer-checked:ring-2 peer-checked:ring-primary-color/20 hover:border-zinc-300 dark:hover:border-zinc-700 border-zinc-200 dark:border-zinc-800">
                                @if($brand->hasMedia('logo'))
                                    <img src="{{ asset($brand->getFirstMediaUrl('logo')) }}" alt="{{ $brand->name }}" class="h-10 w-10 rounded-full object-cover">
                                @else
                                    <div class="h-10 w-10 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center font-bold text-primary-color">
                                        {{ substr($brand->name, 0, 1) }}
                                    </div>
                                @endif
                                <span class="text-sm font-medium">{{ $brand->name }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
                <flux:error name="brand_id" />
            </flux:field>

            @if($brand_id)
                <!-- Type Selection -->
                <flux:field>
                    <flux:label>Select Data Type</flux:label>

                    {{-- Loading skeleton shown while brand_id update is in-flight --}}
                    <div wire:loading wire:target="brand_id" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach(range(1, 3) as $i)
                            <div class="h-11 rounded-xl bg-zinc-100 dark:bg-zinc-800 animate-pulse"></div>
                        @endforeach
                    </div>

                    {{-- Actual type buttons, hidden while loading --}}
                    <div wire:loading.remove wire:target="brand_id">
                        @if($this->types->isNotEmpty())
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach($this->types as $type)
                                    <label class="relative cursor-pointer group" wire:key="type-{{ $type }}">
                                        <input type="radio" wire:model.live="type_filter" value="{{ $type }}" class="sr-only peer">
                                        <div class="p-3 border-2 rounded-xl flex items-center justify-center text-center transition-all peer-checked:border-primary-color peer-checked:bg-primary-color/5 peer-checked:shadow-md peer-checked:ring-2 peer-checked:ring-primary-color/20 hover:border-zinc-300 dark:hover:border-zinc-700 border-zinc-200 dark:border-zinc-800">
                                            <span class="text-sm font-semibold uppercase tracking-wide">{{ $type }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <flux:text class="text-zinc-400 text-sm py-2">No data types available for this provider.</flux:text>
                        @endif
                    </div>
                    <flux:error name="type_filter" />
                </flux:field>

                <!-- Plan Selection -->
                @if($type_filter)
                    <flux:field>
                        <flux:label>Select Data Plan</flux:label>

                        {{-- Loading skeleton shown while type_filter update is in-flight --}}
                        <div wire:loading wire:target="type_filter" class="grid grid-cols-1 gap-3">
                            @foreach(range(1, 3) as $i)
                                <div class="h-16 rounded-xl bg-zinc-100 dark:bg-zinc-800 animate-pulse"></div>
                            @endforeach
                        </div>

                        {{-- Actual plans, hidden while loading --}}
                        <div wire:loading.remove wire:target="type_filter">
                            @if($this->plans->isEmpty())
                                <flux:text class="text-zinc-400 text-sm py-2">No plans available for this type.</flux:text>
                            @else
                                <div class="grid grid-cols-1 gap-3">
                                    @foreach($this->plans as $plan)
                                        <label class="relative cursor-pointer" wire:key="plan-{{ $plan->id }}">
                                            <input type="radio" wire:model.live="plan_id" value="{{ $plan->id }}" class="sr-only peer">
                                            <div class="p-4 border rounded-xl flex justify-between items-center transition-all peer-checked:border-primary-color peer-checked:bg-primary-color/5 peer-checked:shadow-md peer-checked:ring-2 peer-checked:ring-primary-color/20 hover:border-zinc-300 dark:hover:border-zinc-700 border-zinc-200 dark:border-zinc-800">
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-lg">{{ $plan->name ?? $plan->type }}</span>
                                                    <span class="text-xs text-zinc-500 uppercase tracking-widest font-bold">{{ $plan->duration }}</span>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-xl font-black text-primary-color">{{ Number::currency($plan->price) }}</div>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <flux:error name="plan_id" />
                    </flux:field>
                @endif
            @endif

            <!-- Phone Number -->
            <flux:input 
                wire:model="phone_number" 
                label="Phone Number" 
                placeholder="e.g. 08012345678" 
                icon="phone"
            />

            @if($this->selectedPlan)
                <flux:card class="bg-zinc-50 dark:bg-zinc-900 border-dashed p-4 flex justify-between items-center">
                    <div class="text-sm font-medium">Plan Cost:</div>
                    <div class="text-lg font-bold text-primary-color">{{ Number::currency($this->selectedPlan->price) }}</div>
                </flux:card>
            @endif

            <div class="pt-4">
                <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove>Buy Data Bundle</span>
                    <span wire:loading>Processing...</span>
                </flux:button>
            </div>
        </form>
    </flux:card>

    <!-- Wallet Summary -->
    <flux:card class="bg-primary-color/5 border-primary-color/20 flex items-center justify-between p-4">
        <div class="flex items-center gap-3">
            <flux:icon.wallet class="size-5 text-primary-color" />
            <flux:text class="font-medium">Wallet Balance</flux:text>
        </div>
        <flux:text class="text-lg font-bold text-primary-color">
            {{ Number::currency(auth()->user()->wallet->available_balance) }}
        </flux:text>
    </flux:card>
</div>
