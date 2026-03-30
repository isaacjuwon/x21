<?php

use App\Models\Brand;
use App\Models\AirtimePlan;
use App\Models\TopupTransaction;
use App\Enums\Wallets\WalletType;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;

new #[Title('Airtime Purchase')] class extends Component {
    public $brand_id;
    public $phone_number;
    public $amount;

    protected $rules = [
        'brand_id' => 'required|exists:brands,id',
        'phone_number' => 'required|string|min:10',
        'amount' => 'required|numeric|min:50',
    ];

    #[Computed]
    public function brands()
    {
        return Brand::whereHas('airtimePlans', fn($q) => $q->where('status', true))
            ->where('status', true)
            ->get();
    }

    public function buy()
    {
        $this->validate();

        $user = Auth::user();
        $brand = Brand::find($this->brand_id);
        $plan = $brand->airtimePlans()->where('status', true)->first();

        if (!$plan) {
            $this->addError('brand_id', 'No active airtime plan found for this brand.');
            return;
        }

        if ($user->wallet->available_balance < $this->amount) {
            $this->addError('amount', 'Insufficient wallet balance.');
            return;
        }

        try {
            DB::transaction(function () use ($user, $brand, $plan) {
                $user->withdraw($this->amount, WalletType::General, "Airtime Purchase: {$brand->name} ({$this->phone_number})");

                TopupTransaction::create([
                    'user_id' => $user->id,
                    'brand_id' => $brand->id,
                    'plan_id' => $plan->id,
                    'plan_type' => AirtimePlan::class,
                    'amount' => $this->amount,
                    'phone_number' => $this->phone_number,
                    'status' => 'pending',
                    'reference' => 'AIR-'.strtoupper(Str::random(10)),
                ]);
            });

            Flux::toast('Airtime purchase initiated successfully.');
            $this->reset(['amount', 'phone_number', 'brand_id']);
        } catch (\Exception $e) {
            $this->addError('amount', 'An error occurred during the transaction.');
        }
    }
}; ?>

<div class="max-w-2xl mx-auto space-y-6">
    <flux:heading size="xl">Airtime Purchase</flux:heading>
    <flux:subheading>Top up your phone instantly.</flux:subheading>

    <flux:card>
        <form wire:submit="buy" class="space-y-6">
            <!-- Brand Selection -->
            <flux:field>
                <flux:label>Select Network Provider</flux:label>
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

            <!-- Phone Number -->
            <flux:input 
                wire:model="phone_number" 
                label="Phone Number" 
                placeholder="e.g. 08012345678" 
                icon="phone"
            />

            <!-- Amount -->
            <flux:input 
                wire:model="amount" 
                label="Amount" 
                type="number" 
                placeholder="Enter amount" 
                icon="banknotes"
                :hint="'Minimum: ' . Number::currency(50)"
            />

            <div class="pt-4">
                <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove>Buy Airtime</span>
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
