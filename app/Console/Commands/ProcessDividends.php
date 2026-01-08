<?php
// app/Console/Commands/ProcessDividends.php

namespace App\Console\Commands;

use App\Models\Dividend;
use Illuminate\Console\Command;

class ProcessDividends extends Command
{
    protected $signature = 'dividends:process
                            {--dividend= : Specific dividend ID to process}
                            {--chunk=1000 : Chunk size for large datasets}
                            {--pending : Process all pending dividends}';

    protected $description = "Process dividend payouts for all shareholders";

    public function handle(): int
    {
        $dividendId = $this->option("dividend");
        $chunkSize = (int) $this->option("chunk");
        $processPending = $this->option("pending");

        if ($dividendId) {
            // Process specific dividend
            $dividend = Dividend::findOrFail($dividendId);
            return $this->processSingleDividend($dividend, $chunkSize);
        }

        if ($processPending) {
            // Process all pending dividends
            return $this->processPendingDividends($chunkSize);
        }

        $this->error("Please specify either --dividend=ID or --pending");
        return Command::FAILURE;
    }

    protected function processSingleDividend(
        Dividend $dividend,
        int $chunkSize,
    ): int {
        if ($dividend->paid_out) {
            $this->warn(
                "Dividend ID {$dividend->id} has already been paid out.",
            );
            return Command::SUCCESS;
        }

        $this->info("Processing dividend ID: {$dividend->id}");
        $this->info("Amount per share: {$dividend->amount_per_share}");
        $this->info("Currency: {$dividend->currency}");

        // Count shareholders to be paid
        $shareholderCount = \App\Models\Share::where(
            "currency",
            $dividend->currency,
        )
            ->where("quantity", ">", 0)
            ->count();

        $this->info("Found {$shareholderCount} shareholders to pay");

        if ($shareholderCount === 0) {
            $this->warn("No shareholders found for this dividend.");
            $dividend->update(["paid_out" => true]);
            return Command::SUCCESS;
        }

        // Process the dividend
        if ($shareholderCount > 1000) {
            $this->info("Processing in chunks of {$chunkSize}...");
            $dividend->processPayoutInChunks($chunkSize);
        } else {
            $dividend->processPayout();
        }

        // Show statistics
        $stats = $dividend->getPayoutStatistics();
        $this->info("Dividend processing completed!");
        $this->info("Total shareholders paid: {$stats["total_shareholders"]}");
        $this->info("Total shares: {$stats["total_shares"]}");
        $this->info("Total payout amount: {$stats["total_payout"]}");

        return Command::SUCCESS;
    }

    protected function processPendingDividends(int $chunkSize): int
    {
        $pendingDividends = Dividend::where("paid_out", false)
            ->where("payment_date", "<=", now())
            ->get();

        if ($pendingDividends->isEmpty()) {
            $this->info("No pending dividends found.");
            return Command::SUCCESS;
        }

        $this->info("Found {$pendingDividends->count()} pending dividends");

        foreach ($pendingDividends as $dividend) {
            $this->info("Processing dividend ID: {$dividend->id}");
            $this->processSingleDividend($dividend, $chunkSize);
            $this->line(""); // Empty line for separation
        }

        $this->info("All pending dividends processed!");
        return Command::SUCCESS;
    }
}
