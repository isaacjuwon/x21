<?php

namespace App\Notifications\Loans;

use App\Models\Loan;
use App\Settings\SmsSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Number;

class LoanSettledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Loan $loan) {}

    public function via(object $notifiable): array
    {
        $channels = ['database', 'mail'];

        if (app(SmsSettings::class)->sms_loan_settled) {
            $channels[] = 'kudisms';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Loan Has Been Fully Settled')
            ->markdown('mail.loans.settled', [
                'notifiable' => $notifiable,
                'loan' => $this->loan,
            ]);
    }

    public function toSms(object $notifiable): string
    {
        return "Congratulations {$notifiable->name}! Your loan of ".Number::currency($this->loan->principal_amount).' has been fully settled. Thank you!';
    }

    public function toArray(object $notifiable): array
    {
        return [
            'loan_id' => $this->loan->id,
            'status' => $this->loan->status,
        ];
    }
}
