<?php

namespace App\Actions\Services;

use App\Enums\Connectors\VtuConnector;
use App\Enums\Transaction\Status;
use App\Enums\Transaction\Type;
use App\Events\Services\PurchaseServiceEvent;
use App\Integrations\Epins\Entities\PurchaseAirtime;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Models\AirtimePlan;
use App\Models\Transaction;
use App\Support\Result;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AirtimePurchaseAction
{
    public function handle(array $data): Result
    {
        try {
            $user = Auth::user();
            $airtimePlan = AirtimePlan::find($data['plan_id']);

            if (! $airtimePlan) {
                return Result::error(new Exception('Airtime plan not found'));
            }

            // Create a pending transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'amount' => $data['amount'],
                'type' => Type::Payment,
                'status' => Status::Pending,
                'reference' => $data['reference'],
                'transactable_id' => $airtimePlan->id,
                'transactable_type' => AirtimePlan::class,
                'meta' => [
                    'service' => 'airtime',
                    'network' => $data['network'],
                    'phone' => $data['phone'],
                    'ported' => $data['ported'] ?? false,
                    'plan_id' => $data['plan_id'],
                ],
            ]);

            $payload = new PurchaseAirtime(
                network: $data['network'],
                amount: (int) $data['amount'],
                mobileNumber: $data['phone'],
                portedNumber: ($data['ported'] ?? false) ? 'true' : 'false',
                reference: $data['reference'],
            );

            /** @var ServiceResponse $result */
            $result = VtuConnector::default()->connector()->airtime()->purchase($payload);

            if ($result->isSuccessful()) {
                $transaction->markAsSuccess();

                // Dispatch event for successful purchase
                event(new PurchaseServiceEvent(
                    $user,
                    'airtime',
                    $airtimePlan->name,
                    $data['reference'],
                    $data['amount']
                ));

                return Result::ok(['message' => 'Airtime purchase successful.']);
            }

            $message = $result->description['message'] ?? 'Airtime purchase failed.';
            Log::error('Airtime Purchase Failed: '.$message, ['data' => $data, 'response' => (array) $result]);
            $transaction->markAsFailed();

            return Result::error(new Exception($message));
        } catch (Exception $e) {
            Log::error('Error during airtime purchase: '.$e->getMessage(), ['data' => $data, 'exception' => $e]);

            // Mark transaction as failed if it exists
            if (isset($transaction)) {
                $transaction->markAsFailed();
            }

            return Result::error($e);
        }
    }
}
