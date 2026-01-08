<?php

namespace App\Notifications\Loans;

use App\Models\Loan;
use App\Models\LoanPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanPaymentMadeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Loan $loan, public LoanPayment $payment)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Loan Payment Received')
            ->markdown('mail.loans.payment_made', [
                'loan' => $this->loan,
                'payment' => $this->payment,
                'user' => $notifiable
            ]);
    }
}
