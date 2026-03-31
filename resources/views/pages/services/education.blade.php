<?php

use App\Models\Brand;
use App\Models\EducationPlan;
use App\Models\TopupTransaction;
use App\Enums\Wallets\WalletType;
use App\Actions\Vtu\PurchaseEducationAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;

new #[Title('Education Pins')] class extends Component {
    public $brand_id;
    public $plan_id;
    public $quantity = 1;

    protected $rules = [
        'brand_id' => 'required|exists:brands,id',
        'plan_id' => 'required|exists:education_plans,id',
        'quantity' => 'required|integer|min:1|max:5',
    ];

    #[Computed]
    public function brands()
    {
        return Brand::whereHas('educationPlans', fn($q) => $q->where('status', true))
            ->where('status', true)
            ->get();
    }

    #[Computed]
    public function plans()
    {
        if (!$this->brand_id) return collect();

        return EducationPlan::where('brand_id', $this->brand_id)
            ->where('status', true)
            ->get();
    }

    #[Computed]
    public function selectedPlan()
    {
        if (!$this->plan_id) return null;
        return EducationPlan::find($this->plan_id);
    }

    #[Computed]
    public function totalAmount()
    {
        if (!$this->selectedPlan) return 0;
        return $this->selectedPlan->price * $this->quantity;
    }

    public function updatedBrandId()
    {
        $this->reset('plan_id');
    }

    public function buy(PurchaseEducationAction $purchaseAction)
    {
        $this->validate();

        $user = Auth::user();
        $plan = $this->selectedPlan;
        $total = $this->totalAmount;

        if ($user->wallet->available_balance < $total) {
            $this->addError('plan_id', 'Insufficient wallet balance.');
            return;
        }

        try {
            $transaction = DB::transaction(function () use ($user, $plan, $total) {
                $topup = TopupTransaction::create([
                    'user_id' => $user->id,
                    'brand_id' => $plan->brand_id,
                    'plan_id' => $plan->id,
                    'plan_type' => EducationPlan::class,
                    'amount' => $total,
                    'quantity' => $this->quantity,
                    'status' => 'pending',
                    'reference' => 'EDU-'.strtoupper(Str::random(10)),
                ]);

                $user->withdraw($total, WalletType::General, "Education Pin: {$plan->brand->name} {$plan->type} x{$this->quantity}", $topup);

                return $topup;
            });

            $purchaseAction->handle($transaction);

            Flux::toast('Education PIN purchase initiated successfully.');
            $this->reset(['plan_id', 'quantity', 'brand_id']);
        } catch (\Exception $e) {
            $this->addError('plan_id', 'An error occurred during the transaction: ' . $e->getMessage());
        }
    }
}; ?>

<div class="max-w-2xl mx-auto space-y-6">
    <flux:heading size="xl">Education PINs</flux:heading>
    <flux:subheading>Buy exam result checker PINs (WAEC, JAMB, etc.).</flux:subheading>

    <flux:card>
        <form wire:submit="buy" class="space-y-6">
            <!-- Brand Selection -->
            <flux:field>
                <flux:label>Select Exam Body</flux:label>
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
                    <flux:label>Select Pin Type</flux:label>
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

            <!-- Quantity -->
            <flux:input 
                wire:model.live="quantity" 
                label="Quantity" 
                type="number" 
                min="1" 
                max="5"
                placeholder="1" 
                icon="shopping-cart"
            />

            @if($this->selectedPlan)
                <flux:card class="bg-zinc-50 dark:bg-zinc-900 border-dashed p-4 flex justify-between items-center">
                    <div class="text-sm font-medium">Total Cost:</div>
                    <div class="text-lg font-bold text-primary-color">{{ Number::currency($this->totalAmount) }}</div>
                </flux:card>
            @endif

            <div class="pt-4">
                <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove>Purchase PIN</span>
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
