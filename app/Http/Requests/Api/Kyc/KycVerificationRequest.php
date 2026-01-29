<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Kyc;

use App\Enums\Kyc\Type;
use App\Http\Payloads\Kyc\KycVerificationPayload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KycVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::enum(Type::class)],
            'id_number' => ['required', 'string'],
            'dob' => ['nullable', 'date'],
            'phone' => ['nullable', 'string'],
            'document_path' => ['nullable', 'string'],
        ];
    }

    public function payload(): KycVerificationPayload
    {
        return new KycVerificationPayload(
            type: $this->string('type')->toString(),
            idNumber: $this->string('id_number')->toString(),
            dob: $this->filled('dob') ? $this->string('dob')->toString() : null,
            phone: $this->filled('phone') ? $this->string('phone')->toString() : null,
            documentPath: $this->filled('document_path') ? $this->string('document_path')->toString() : null,
        );
    }
}
