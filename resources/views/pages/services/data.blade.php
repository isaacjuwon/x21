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
    public function plans()
    {
        if (!$this->brand_id) return collect();

        return DataPlan::where('brand_id', $this->brand_id)
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
                    'amount' => $plan->price,
                    'phone_number' => $this->phone_number,
                    'status' => 'pending',
                    'reference' => 'DAT-'.strtoupper(Str::random(10)),
                ]);

                $user->withdraw($plan->price, WalletType::General, "Data Purchase: {$plan->brand->name} {$plan->type} ({$this->phone_number})", $topup);

                return $topup;
            });

            $purchaseAction->handle($transaction);

            Flux::toast('Data purchase initiated successfully.');
            $this->reset(['plan_id', 'phone_number', 'brand_id']);
        } catch (\Exception $e) {
            $this->addError('plan_id', 'An error occurred during the transaction: ' . $e->getMessage());
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
                                    <img src="{{ $brand->getFirstMediaUrl('logo') }}" alt="{{ $brand->name }}" class="h-10 w-10 rounded-full object-cover">
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
                <!-- Plan Selection -->
                <flux:field>
                    <flux:label>Select Data Plan</flux:label>
                    <div class="grid grid-cols-1 gap-3">
                        @foreach($this->plans as $plan)
                            <label class="relative cursor-pointer">
                                <input type="radio" wire:model.live="plan_id" value="{{ $plan->id }}" class="sr-only peer">
                                <div class="p-4 border rounded-xl flex justify-between items-center transition-all peer-checked:border-primary-color peer-checked:bg-primary-color/5 peer-checked:shadow-md peer-checked:ring-2 peer-checked:ring-primary-color/20 hover:border-zinc-300 dark:hover:border-zinc-700 border-zinc-200 dark:border-zinc-800">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-lg">{{ $plan->type }}</span>
                                        <span class="text-xs text-zinc-500 uppercase tracking-widest font-bold">{{ $plan->duration }}</span>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xl font-black text-primary-color">{{ Number::currency($plan->price) }}</div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <flux:error name="plan_id" />
                </flux:field>
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
