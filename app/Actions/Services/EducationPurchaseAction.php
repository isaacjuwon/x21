<?php

namespace App\Actions\Services;

use App\Enums\Connectors\VtuConnector;
use App\Enums\Transaction\Status;
use App\Enums\Transaction\Type;
use App\Events\Services\PurchaseServiceEvent;
use App\Integrations\Epins\Entities\PurchaseExam;
use App\Integrations\Epins\Entities\ServiceResponse;
use App\Models\EducationPlan;
use App\Models\Transaction;
use App\Support\Result;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EducationPurchaseAction
{
    public function handle(array $data): Result
    {
        try {
            $user = Auth::user();
            $educationPlan = EducationPlan::find($data['plan_id']);

            if (! $educationPlan) {
                return Result::error(new Exception('Education plan not found'));
            }

            // Create a pending transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'amount' => $data['amount'],
                'type' => Type::Payment,
                'status' => Status::Pending,
                'reference' => $data['reference'],
                'transactable_id' => $educationPlan->id,
                'transactable_type' => EducationPlan::class,
                'meta' => [
                    'service' => 'education',
                    'operator' => $data['operator_name'],
                    'plan_code' => $data['plan_code'],
                    'plan_id' => $data['plan_id'],
                ],
            ]);

            $payload = new PurchaseExam(
                service: $data['operator_name'],
                numberOfPins: 1, // Defaulting to 1 as per typical usage
                reference: $data['reference'],
            );

            /** @var ServiceResponse $result */
            $result = VtuConnector::default()->connector()->education()->purchase($payload);

            if ($result->isSuccessful()) {
                $transaction->markAsSuccess();

                // Dispatch event for successful purchase
                event(new PurchaseServiceEvent(
                    $user,
                    'education',
                    $educationPlan->name,
                    $data['reference'],
                    $data['amount']
                ));

                return Result::ok([
                    'message' => 'Education PIN purchase successful.',
                    'data' => $result->description,
                ]);
            }

            $message = $result->description['message'] ?? 'Education PIN purchase failed.';
            Log::error('Education PIN Purchase Failed: '.$message, ['data' => $data, 'response' => (array) $result]);
            $transaction->markAsFailed();

            return Result::error(new Exception($message));
        } catch (Exception $e) {
            Log::error('Error during education PIN purchase: '.$e->getMessage(), ['data' => $data, 'exception' => $e]);

            // Mark transaction as failed if it exists
            if (isset($transaction)) {
                $transaction->markAsFailed();
            }

            return Result::error($e);
        }
    }
}
