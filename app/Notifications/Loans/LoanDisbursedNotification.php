<?php

namespace App\Notifications\Loans;

use App\Models\Loan;
use App\Settings\SmsSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Number;

class LoanDisbursedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Loan $loan) {}

    public function via(object $notifiable): array
    {
        $channels = ['database', 'mail'];

        if (app(SmsSettings::class)->sms_loan_disbursed) {
            $channels[] = 'kudisms';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Loan Has Been Disbursed')
            ->markdown('mail.loans.disbursed', [
                'notifiable' => $notifiable,
                'loan' => $this->loan,
            ]);
    }

    public function toSms(object $notifiable): string
    {
        return "Hi {$notifiable->name}, ".Number::currency($this->loan->principal_amount).' has been disbursed to your wallet. Check your account for details.';
    }

    public function toArray(object $notifiable): array
    {
        return [
            'loan_id' => $this->loan->id,
            'status' => $this->loan->status,
        ];
    }
}
