<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Wallet\FetchBanksAction;
use App\Actions\Wallet\FundWalletAction;
use App\Actions\Wallet\TransferFundAction;
use App\Actions\Wallet\WithdrawWalletAction;
use App\Http\Requests\Api\Wallet\DepositRequest;
use App\Http\Requests\Api\Wallet\FundWalletRequest;
use App\Http\Requests\Api\Wallet\TransferRequest;
use App\Http\Requests\Api\Wallet\WithdrawRequest;
use App\Http\Resources\WalletResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends ApiController
{
    /**
     * Get user wallet information.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->successResponse(new WalletResource($request->user()), 'Wallet retrieved successfully');
    }

    /**
     * Deposit funds into wallet.
     */
    public function deposit(DepositRequest $request): JsonResponse
    {
        $payload = $request->payload();

        DB::transaction(function () use ($request, $payload) {
            $request->user()->deposit($payload->amount); // Assuming 'deposit' method exists on User/Wallet trait
        });

        return $this->successResponse(null, 'Deposit successful.');
    }

    /**
     * Fund wallet using external payment gateway.
     */
    public function fund(FundWalletRequest $request, FundWalletAction $action): JsonResponse
    {
        $payload = $request->payload();

        $result = DB::transaction(function () use ($action, $payload) {
             return $action->handle($payload->toArray());
        });

        if (!$result->success) {
             return $this->errorResponse($result->message, 400);
        }

        return $this->successResponse($result->data, 'Funding initialized successfully');
    }

    /**
     * Withdraw funds from wallet.
     */
    public function withdraw(WithdrawRequest $request, WithdrawWalletAction $action): JsonResponse
    {
        $payload = $request->payload();

        $result = DB::transaction(function () use ($action, $payload) {
            return $action->handle($payload->toArray());
        });

        if (!$result->success) {
            return $this->errorResponse($result->message, 400);
        }

        return $this->successResponse($result->data, 'Withdrawal successful');
    }

    /**
     * Get list of banks.
     */
    public function getBanks(FetchBanksAction $action): JsonResponse
    {
        $banks = $action->handle();

        return $this->successResponse($banks, 'Banks retrieved successfully');
    }

    /**
     * Transfer funds to another user.
     */
    public function transfer(TransferRequest $request, TransferFundAction $action): JsonResponse
    {
        $payload = $request->payload();

        $result = DB::transaction(function () use ($action, $request, $payload) {
            return $action->handle($request->user(), $payload->toArray());
        });

        if (!$result->success) {
             return $this->errorResponse($result->message, 400);
        }

        return $this->successResponse(null, 'Transfer successful.');
    }
}
