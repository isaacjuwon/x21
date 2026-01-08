<?php

namespace App\Actions\Services;

use App\Enums\Connectors\VtuConnector;
use App\Integrations\Epins\Entities\PurchaseElectricity;
use App\Integrations\Epins\Entities\ServiceResponse;
use Illuminate\Support\Facades\Log;
use App\Support\Result;
use App\Models\Transaction;
use App\Models\ElectricityPlan;
use App\Enums\Transaction\Type;
use App\Enums\Transaction\Status;
use Illuminate\Support\Facades\Auth;
use App\Events\Services\PurchaseServiceEvent;
use Exception;

class ElectricityPurchaseAction
{
    public function handle(array $data): Result
    {
        try {
            $user = Auth::user();
            $electricityPlan = ElectricityPlan::where('brand_id', $data['network_id'])->first();

            // Create a pending transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'amount' => $data['amount'],
                'type' => Type::Payment,
                'status' => Status::Pending,
                'reference' => $data['reference'],
                'transactionable_id' => $electricityPlan->id,
                'transactionable_type' => ElectricityPlan::class,
                'meta' => [
                    'service' => 'electricity',
                    'network' => $data['network_name'],
                    'meter_number' => $data['phone'],
                    'meter_type' => $data['meter_type'],
                    'network_id' => $data['network_id'],
                ],
            ]);

            $payload = new PurchaseElectricity(
                service: $data['network_name'],
                meterNumber: $data['phone'],
                amount: (int) $data['amount'],
                reference: $data['reference'],
                phone: $data['customer_phone'] ?? null,
            );

            /** @var ServiceResponse $result */
            $result = VtuConnector::default()->connector()->electricity()->purchase($payload);

            if ($result->isSuccessful()) {
                $transaction->markAsSuccess();
                
                // Dispatch event for successful purchase
                event(new PurchaseServiceEvent(
                    $user,
                    'electricity',
                    $electricityPlan->name,
                    $data['reference'],
                    $data['amount']
                ));
                
                return Result::ok([
                    'message' => 'Electricity purchase successful.',
                    'data' => $result->description
                ]);
            }

            $message = $result->description['message'] ?? 'Electricity purchase failed.';
            Log::error('Electricity Purchase Failed: ' . $message, ['data' => $data, 'response' => (array) $result]);
            $transaction->markAsFailed();
            
            return Result::error(new Exception($message));
        } catch (Exception $e) {
            Log::error('Error during electricity purchase: ' . $e->getMessage(), ['data' => $data, 'exception' => $e]);
            
            // Mark transaction as failed if it exists
            if (isset($transaction)) {
                $transaction->markAsFailed();
            }
            
            return Result::error($e);
        }
    }
}