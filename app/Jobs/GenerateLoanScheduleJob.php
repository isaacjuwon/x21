<?php

namespace App\Jobs;

use App\Actions\Loans\GenerateLoanScheduleAction;
use App\Models\Loan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateLoanScheduleJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Loan $loan) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        app(GenerateLoanScheduleAction::class)->handle($this->loan);
    }
}
