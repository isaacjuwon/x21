<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class RecordApiRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $type,
        protected string $method,
        protected string $url,
        protected array $payload,
        protected array $response,
        protected ?int $userId = null,
        protected ?string $reference = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::table('api_requests')->insert([
            'type' => $this->type,
            'method' => $this->method,
            'url' => $this->url,
            'payload' => json_encode($this->payload),
            'response' => json_encode($this->response),
            'user_id' => $this->userId,
            'reference' => $this->reference,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
