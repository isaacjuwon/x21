<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Plans\ServiceType;
use App\Services\Vtu\PlanSyncService;
use Illuminate\Console\Command;

final class SyncVtuPlansCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vtu:sync-plans 
                            {--provider=all : Target provider: vtugate, vtpass, or all} 
                            {--service=all : Service type: data, cable, education, or all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch, normalize, and populate unified plans from upstream VTU providers.';

    public function handle(PlanSyncService $syncService): int
    {
        $providerOpt = strtolower((string) $this->option('provider'));
        $serviceOpt = strtolower((string) $this->option('service'));

        $serviceType = match ($serviceOpt) {
            'data' => ServiceType::Data,
            'cable' => ServiceType::Cable,
            'education' => ServiceType::Education,
            default => null,
        };

        $this->info("Starting VTU Plan synchronization [Provider: {$providerOpt}, Service: {$serviceOpt}]...");

        $reports = [];

        if ($providerOpt === 'all') {
            $reports = $syncService->syncAll($serviceType);
        } else {
            $reports[$providerOpt] = $syncService->syncProvider($providerOpt, $serviceType);
        }

        $rows = [];
        foreach ($reports as $name => $report) {
            $rows[] = [
                ucfirst($name),
                $report->created,
                $report->merged,
                $report->updated,
                $report->total(),
                count($report->errors) > 0 ? implode(', ', array_slice($report->errors, 0, 2)) : 'None',
            ];
        }

        $this->table(['Provider', 'Created', 'Merged', 'Updated', 'Total Processed', 'Errors'], $rows);
        $this->info('Plan synchronization complete.');

        return self::SUCCESS;
    }
}
