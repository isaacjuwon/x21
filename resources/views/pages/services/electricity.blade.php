<?php

use App\Models\Brand;
use App\Models\ElectricityPlan;
use App\Models\TopupTransaction;
use App\Enums\Wallets\WalletType;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;

new #[Title('Electricity Bill Payment')] class extends Component {
    public $brand_id;
    public $meter_type = 'Prepaid';
    public $meter_number;
    public $amount;

    protected $rules = [
        'brand_id' => 'required|exists:brands,id',
        'meter_type' => 'required|in:Prepaid,Postpaid',
        'meter_number' => 'required|string|min:8',
        'amount' => 'required|numeric|min:500',
    ];

    #[Computed]
    public function brands()
    {
        return Brand::whereHas('electricityPlans', fn($q) => $q->where('status', true))
            ->where('status', true)
            ->get();
    }

    public function pay()
    {
        $this->validate();

        $user = Auth::user();
        $brand = Brand::find($this->brand_id);
        $plan = $brand->electricityPlans()->where('status', true)->first();

        if (!$plan) {
            $this->addError('brand_id', 'No active electricity plan found for this brand.');
            return;
        }

        if ($user->wallet->available_balance < $this->amount) {
            $this->addError('amount', 'Insufficient wallet balance.');
            return;
        }

        try {
            DB::transaction(function () use ($user, $brand, $plan) {
                $user->withdraw($this->amount, WalletType::General, "Electricity Payment: {$brand->name} {$this->meter_type} ({$this->meter_number})");

                TopupTransaction::create([
                    'user_id' => $user->id,
                    'brand_id' => $brand->id,
                    'plan_id' => $plan->id,
                    'plan_type' => ElectricityPlan::class,
                    'amount' => $this->amount,
                    'meter_number' => $this->meter_number,
                    'meter_type' => $this->meter_type,
                    'status' => 'pending',
                    'reference' => 'ELE-'.strtoupper(Str::random(10)),
                ]);
            });

            Flux::toast('Electricity bill payment initiated successfully.');
            $this->reset(['amount', 'meter_number', 'brand_id']);
        } catch (\Exception $e) {
            $this->addError('amount', 'An error occurred during the transaction.');
        }
    }
}; ?>

<div class="max-w-2xl mx-auto space-y-6">
    <flux:heading size="xl">Electricity Bill Payment</flux:heading>
    <flux:subheading>Pay your electricity bills quickly and securely.</flux:subheading>

    <flux:card>
        <form wire:submit="pay" class="space-y-6">
            <!-- Brand Selection -->
            <flux:field>
                <flux:label>Select Disco Provider</flux:label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach($this->brands as $brand)
                        <label class="relative cursor-pointer group">
                            <input type="radio" wire:model.live="brand_id" value="{{ $brand->id }}" class="sr-only peer">
                            <div class="p-4 border-2 rounded-xl flex flex-col items-center gap-2 transition-all peer-checked:border-primary-color peer-checked:bg-primary-color/5 hover:border-zinc-300 dark:hover:border-zinc-700 border-zinc-200 dark:border-zinc-800">
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

            <!-- Meter Type -->
            <flux:field>
                <flux:label>Meter Type</flux:label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <flux:radio wire:model="meter_type" value="Prepaid" label="Prepaid" />
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <flux:radio wire:model="meter_type" value="Postpaid" label="Postpaid" />
                    </label>
                </div>
                <flux:error name="meter_type" />
            </flux:field>

            <!-- Meter Number -->
            <flux:input 
                wire:model="meter_number" 
                label="Meter Number" 
                placeholder="Enter meter number" 
                icon="identification"
            />

            <!-- Amount -->
            <flux:input 
                wire:model="amount" 
                label="Amount" 
                type="number" 
                placeholder="Enter amount" 
                icon="banknotes"
                :hint="'Minimum: ' . Number::currency(500)"
            />

            <div class="pt-4">
                <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove>Pay Bill</span>
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
