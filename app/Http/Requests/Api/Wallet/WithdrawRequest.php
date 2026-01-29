<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Wallet;

use App\Actions\GenerateReferenceAction;
use App\Http\Payloads\Wallet\WithdrawPayload;
use Illuminate\Foundation\Http\FormRequest;

class WithdrawRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1'],
            'account_number' => ['required', 'string', 'size:10'], // Nigerian NKS usually 10
            'bank_code' => ['required', 'string'],
        ];
    }

    public function payload(): WithdrawPayload
    {
        return new WithdrawPayload(
            amount: (float) $this->float('amount'),
            accountNumber: $this->string('account_number')->toString(),
            bankCode: $this->string('bank_code')->toString(),
            reference: (string) app(GenerateReferenceAction::class)->handle('WAL-WD'),
        );
    }
}
