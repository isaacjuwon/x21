<?php

namespace App\Listeners;

use App\Events\Loans\LoanApplied;
use App\Events\Loans\LoanDisbursed;
use App\Events\Loans\LoanFullyPaid;
use App\Events\Loans\LoanPaymentMade;
use App\Notifications\Loans\LoanAppliedNotification;
use App\Notifications\Loans\LoanDisbursedNotification;
use App\Notifications\Loans\LoanFullyPaidNotification;
use App\Notifications\Loans\LoanPaymentMadeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendLoanNotifications implements ShouldQueue
{
    public function subscribe($events): array
    {
        return [
            LoanApplied::class => 'handleLoanApplied',
            LoanDisbursed::class => 'handleLoanDisbursed',
            LoanPaymentMade::class => 'handleLoanPaymentMade',
            LoanFullyPaid::class => 'handleLoanFullyPaid',
        ];
    }

    public function handleLoanApplied(LoanApplied $event): void
    {
        $event->user->notify(new LoanAppliedNotification($event->loan));
    }

    public function handleLoanDisbursed(LoanDisbursed $event): void
    {
        $event->user->notify(new LoanDisbursedNotification($event->loan));
    }

    public function handleLoanPaymentMade(LoanPaymentMade $event): void
    {
        $event->user->notify(new LoanPaymentMadeNotification($event->loan, $event->payment));
    }

    public function handleLoanFullyPaid(LoanFullyPaid $event): void
    {
        $event->user->notify(new LoanFullyPaidNotification($event->loan));
    }
}
