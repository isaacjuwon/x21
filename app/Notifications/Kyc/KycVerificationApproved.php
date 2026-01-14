<?php

namespace App\Notifications\Kyc;

use App\Models\KycVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KycVerificationApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected KycVerification $kycVerification
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your KYC Verification has been Approved ✓')
            ->markdown('mail.kyc.verification-approved', [
                'kycVerification' => $this->kycVerification,
                'notifiable' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'verification_id' => $this->kycVerification->id,
            'type' => $this->kycVerification->type,
            'status' => $this->kycVerification->status->value,
        ];
    }
}
