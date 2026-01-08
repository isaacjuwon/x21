<?php

namespace App\Actions\Services;

use App\Enums\Connectors\VtuConnector;
use App\Enums\Transaction\Status;
use App\Enums\Transaction\Type;
use App\Events\Services\PurchaseServiceEvent;
use App\Integrations\Epins\Entities\PurchaseData;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Models\DataPlan;
use App\Models\Transaction;
use App\Support\Result;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DataPurchaseAction
{
    public function handle(array $data): Result
    {
        try {
            $user = Auth::user();
            $dataPlan = DataPlan::find($data['plan_id']);

            if (! $dataPlan) {
                return Result::error(new Exception('Data plan not found'));
            }

            // Create a pending transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'amount' => $data['amount'],
                'type' => Type::Payment,
                'status' => Status::Pending,
                'reference' => $data['reference'],
                'transactable_id' => $dataPlan->id,
                'transactable_type' => DataPlan::class,
                'meta' => [
                    'service' => 'data',
                    'network' => $data['network'],
                    'phone' => $data['phone'],
                    'plan_code' => $data['plan_code'],
                    'ported' => $data['ported'] ?? false,
                    'plan_id' => $data['plan_id'],
                ],
            ]);

            $payload = new PurchaseData(
                network: $data['network'],
                mobileNumber: $data['phone'],
                dataCode: $data['plan_code'],
                reference: $data['reference'],
            );

            /** @var ServiceResponse $result */
            $result = VtuConnector::default()->connector()->data()->purchase($payload);

            if ($result->isSuccessful()) {
                $transaction->markAsSuccess();

                // Dispatch event for successful purchase
                event(new PurchaseServiceEvent(
                    $user,
                    'data',
                    $dataPlan->name,
                    $data['reference'],
                    $data['amount']
                ));

                return Result::ok(['message' => 'Data purchase successful.']);
            }

            $message = $result->description['message'] ?? 'Data purchase failed.';
            Log::error('Data Purchase Failed: '.$message, ['data' => $data, 'response' => (array) $result]);
            $transaction->markAsFailed();

            return Result::error(new Exception($message));
        } catch (Exception $e) {
            Log::error('Error during data purchase: '.$e->getMessage(), ['data' => $data, 'exception' => $e]);

            // Mark transaction as failed if it exists
            if (isset($transaction)) {
                $transaction->markAsFailed();
            }

            return Result::error($e);
        }
    }
}
