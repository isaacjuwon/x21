<?php

namespace App\Actions\Account;

use App\Attribute\Account;
use App\Integrations\Paystack\PaystackConnector;
use App\Models\User;
use App\Models\Account as UserAccount;
use App\Integrations\Paystack\Entities\CreateDedicatedVirtualAccount;
use App\Support\Result;
use Illuminate\Support\Facades\Log;
use Exception;

class GenerateVirtualAccount
{
    public function __construct(
        #[Account] protected PaystackConnector $connector
    ) {}

    public function handle(User $user): Result
    {
        try {
            // Validate user has required information
            if (empty($user->first_name) || empty($user->last_name)) {
                return Result::error(
                    new Exception('User must have first name and last name')
                );
            }

            // Check if user already has an account
            if ($user->account) {
                return Result::error(
                    new Exception('User already has a virtual account')
                );
            }

            // Prepare data for Paystack API
            $customerCode = $user->email; // Use user's email as customer code for Paystack API

            $virtualAccountEntity = CreateDedicatedVirtualAccount::withCustomerDetails(
                customer: $customerCode,
                firstName: $user->first_name,
                lastName: $user->last_name,
                phone: $user->phone_number ?? null // Assuming user has a phone_number field
            );

            // Call Paystack API to create dedicated virtual account
            $response = $this->connector->dedicatedVirtualAccounts()->create($virtualAccountEntity);

            if ($response->isSuccessful()) {
                $data = $response->data;
                $accountNumber = $data->account_number;
                $bankName = $data->bank['name'];
                $bankId = $data->bank['id'];
                $accountName = $data->account_name;
                $paystackCustomerCode = $data->customer['customer_code'] ?? null; // Get customer code from API response

                // Store virtual account details in the database
                $account = UserAccount::create([
                    'user_id' => $user->id,
                    'account_name' => $accountName,
                    'account_number' => $accountNumber,
                    'bank_name' => $bankName,
                    'bank_number' => (string) $bankId, // Store bank ID as string
                    'paystack_customer_code' => $paystackCustomerCode, // Store Paystack customer code
                ]);

                return Result::ok($account);
            } else {
                $message = $response->message ?? 'Failed to create virtual account with Paystack.';
                Log::error('Paystack Virtual Account Creation Failed: ' . $message, ['user_id' => $user->id, 'response' => (array) $response->data]);
                
                return Result::error(
                    new Exception($message)
                );
            }
        } catch (Exception $e) {
            $message = 'Error generating virtual account: ' . $e->getMessage();
            Log::error($message, ['user_id' => $user->id, 'exception' => $e]);
            
            return Result::error($e);
        }
    }
}