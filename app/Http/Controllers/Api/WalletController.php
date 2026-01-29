<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Wallet\FetchBanksAction;
use App\Actions\Wallet\FundWalletAction;
use App\Actions\Wallet\TransferFundAction;
use App\Actions\Wallet\WithdrawWalletAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Wallet\DepositRequest;
use App\Http\Requests\Api\Wallet\FundWalletRequest;
use App\Http\Requests\Api\Wallet\TransferRequest;
use App\Http\Requests\Api\Wallet\WithdrawRequest;
use App\Http\Resources\WalletResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    /**
     * Get user wallet information.
     */
    public function index(Request $request): WalletResource
    {
        return new WalletResource($request->user());
    }

    /**
     * Deposit funds into wallet.
     */
    public function deposit(DepositRequest $request): \Illuminate\Http\JsonResponse
    {
        $payload = $request->payload();

        DB::transaction(function () use ($request, $payload) {
            $request->user()->deposit($payload->amount); // Assuming 'deposit' method exists on User/Wallet trait
        });

        return response()->json(['message' => 'Deposit successful.']);
    }

    /**
     * Fund wallet using external payment gateway.
     */
    public function fund(FundWalletRequest $request, FundWalletAction $action): \Illuminate\Http\JsonResponse
    {
        $payload = $request->payload();

        // FundWalletAction::handle takes array $data
        // It initializes transaction. Transaction wrapping might be handled inside or not needed for just init.
        // Guidelines say wrap model queries. initialize creates Transaction model.
        
        $result = DB::transaction(function () use ($action, $payload) {
             return $action->handle($payload->toArray());
        });

        if (!$result->success) {
             return response()->json(['message' => 'Funding initialization failed.', 'error' => $result->message], 400);
        }

        return response()->json($result->data);
    }

    /**
     * Withdraw funds from wallet.
     */
    public function withdraw(WithdrawRequest $request, WithdrawWalletAction $action): \Illuminate\Http\JsonResponse
    {
        $payload = $request->payload();

        // Using WithdrawWalletAction
        $result = DB::transaction(function () use ($action, $payload) {
            return $action->handle($payload->toArray());
        });

        if (!$result->success) {
            return response()->json(['message' => $result->message], 400);
        }

        return response()->json($result->data);
    }

    /**
     * Get list of banks.
     */
    public function getBanks(FetchBanksAction $action): \Illuminate\Http\JsonResponse
    {
        $banks = $action->handle();

        return response()->json(['data' => $banks]);
    }

    /**
     * Transfer funds to another user.
     */
    public function transfer(TransferRequest $request, TransferFundAction $action): \Illuminate\Http\JsonResponse
    {
        $payload = $request->payload();

        // TransferFundAction uses handle(User $sender, array $data).
        // It likely handles transactions internally or we should wrap it?
        // Let's wrap it to be safe or just call it.
        // Guidelines say "Action should be ... Model queries create or update should be wrap in database transaction."
        // If Action doesn't have it, we should add it? But user said "dont update action class".
        // Use DB::transaction here.
        
        $result = DB::transaction(function () use ($action, $request, $payload) {
            return $action->handle($request->user(), $payload->toArray());
        });

        if (!$result->success) {
             return response()->json(['message' => $result->message], 400);
        }

        return response()->json(['message' => 'Transfer successful.']);
    }
}
