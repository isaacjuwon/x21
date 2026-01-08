<?php

namespace App\Actions\Services;

use App\Enums\Connectors\VtuConnector;
use App\Integrations\Epins\Entities\PurchaseCable;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use App\Enums\Transaction\Type;
use App\Enums\Transaction\Status;
use Illuminate\Support\Facades\Auth;
use App\Models\CablePlan;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Support\Result;
use App\Events\Services\PurchaseServiceEvent;
use Exception;

class CablePurchaseAction
{
    public function handle(array $data): Result
    {
        try {
            $user = Auth::user();
            $cablePlan = CablePlan::find($data['plan_id']);

            if (!$cablePlan) {
                return Result::error(new Exception('Cable plan not found'));
            }

            // Create a pending transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'amount' => $data['amount'],
                'type' => Type::Payment,
                'status' => Status::Pending,
                'reference' => $data['reference'],
                'transactable_id' => $cablePlan->id,
                'transactable_type' => CablePlan::class,
                'meta' => [
                    'service' => 'cable',
                    'operator' => $data['operator_name'],
                    'smartcard_number' => $data['smartcard_number'],
                    'plan_code' => $data['plan_code'],
                    'plan_id' => $data['plan_id'],
                ],
            ]);

            $payload = new PurchaseCable(
                service: $data['operator_name'],
                smartcardNumber: $data['smartcard_number'],
                variationCode: $data['plan_code'],
                reference: $data['reference'],
                phone: $data['customer_phone'] ?? null,
            );

            /** @var ServiceResponse $result */
            $result = VtuConnector::default()->connector()->cable()->purchase($payload);

           if ($result->isSuccessful()) {
                $transaction->markAsSuccess();
                
                // Dispatch event for successful purchase
                event(new PurchaseServiceEvent(
                    $user,
                    'cable',
                    $cablePlan->name,
                    $data['reference'],
                    $data['amount']
                ));
                
                return Result::ok([
                    'message' => 'Cable subscription successful.',
                    'data' => $result->description
                ]);
            }

            $message = $result->description['message'] ?? 'Cable subscription failed.';
            Log::error('Cable Purchase Failed: ' . $message, ['data' => $data, 'response' => (array) $result]);
            $transaction->markAsFailed();
            
            return Result::error(new Exception($message));
        } catch (Exception $e) {
            Log::error('Error during cable purchase: ' . $e->getMessage(), ['data' => $data, 'exception' => $e]);
            
            // Mark transaction as failed if it exists
            if (isset($transaction)) {
                $transaction->markAsFailed();
            }
            
            return Result::error($e);
        }
    }
}