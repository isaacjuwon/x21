<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Plans\ServiceType;
use App\Models\Concerns\HasProviderCodes;
use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    use HasProviderCodes;

    protected $with = ['providerCodes'];

    protected $fillable = [
        'brand_id',
        'service_type',
        'name',
        'type',
        'api_code',
        'price',
        'cost_price',
        'duration',
        'status',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'service_type' => ServiceType::class,
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'status' => 'boolean',
            'meta' => 'array',
        ];
    }

    /**
     * Associated Brand / Operator.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Check if the plan is active and available.
     */
    public function isAvailable(): bool
    {
        return (bool) $this->status;
    }

    /**
     * Calculate projected profit margin.
     */
    public function profitMargin(): float
    {
        if ($this->cost_price === null) {
            return 0.0;
        }

        return (float) ($this->price - $this->cost_price);
    }

    /**
     * Scope for active plans.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Assign or update a provider-specific API code for failover swapping.
     */
    public function setProviderCode(string $provider, string $code): PlanProviderCode
    {
        /** @var PlanProviderCode $record */
        $record = $this->providerCodes()->updateOrCreate(
            ['provider' => strtolower(trim($provider))],
            ['code' => trim($code)],
        );

        $this->load('providerCodes');

        return $record;
    }

    /**
     * Bulk sync provider codes for failover: ['vtugate' => 'CODE1', 'vtpass' => 'CODE2'].
     *
     * @param  array<string, string>  $codes
     */
    public function syncProviderCodes(array $codes): static
    {
        foreach ($codes as $provider => $code) {
            $this->setProviderCode((string) $provider, (string) $code);
        }

        return $this->load('providerCodes');
    }

    /**
     * Check if a provider code exists for the given provider.
     */
    public function hasProviderCode(string $provider): bool
    {
        return $this->apiCodeFor($provider) !== null;
    }

    /**
     * Remove a provider code mapping.
     */
    public function removeProviderCode(string $provider): bool
    {
        $deleted = (bool) $this->providerCodes()
            ->where('provider', strtolower(trim($provider)))
            ->delete();

        if ($deleted) {
            $this->load('providerCodes');
        }

        return $deleted;
    }

    /**
     * Scope for a specific service type.
     */
    public function scopeForService(Builder $query, ServiceType|string $serviceType): Builder
    {
        $value = $serviceType instanceof ServiceType ? $serviceType->value : strtolower(trim($serviceType));

        return $query->where('service_type', $value);
    }
}
