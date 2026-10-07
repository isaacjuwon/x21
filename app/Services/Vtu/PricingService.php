<?php

declare(strict_types=1);

namespace App\Services\Vtu;

use App\Models\Plan;
use InvalidArgumentException;

final class PricingService
{
    /**
     * Calculate selling price for a plan or given cost price.
     *
     * @param  Plan|float  $planOrCost  The plan instance or raw cost price.
     * @param  float|null  $customPrice  Optional manual price override.
     * @param  float  $markupPercentage  Default percentage markup (e.g. 0.05 for 5%).
     * @param  int  $roundToNearest  Round to nearest naira increment (e.g. 5 or 10).
     */
    public function calculate(
        Plan|float $planOrCost,
        ?float $customPrice = null,
        float $markupPercentage = 0.05,
        int $roundToNearest = 5,
    ): float {
        $costPrice = $planOrCost instanceof Plan ? (float) ($planOrCost->cost_price ?? 0) : $planOrCost;

        if ($costPrice < 0) {
            throw new InvalidArgumentException('Cost price cannot be negative.');
        }

        // 1. Custom price override (if provided or defined on plan meta)
        if ($customPrice !== null && $customPrice > 0) {
            return max($customPrice, $costPrice);
        }

        if ($planOrCost instanceof Plan && isset($planOrCost->meta['custom_price'])) {
            $planCustom = (float) $planOrCost->meta['custom_price'];
            if ($planCustom > 0) {
                return max($planCustom, $costPrice);
            }
        }

        // 2. Rule-based percentage markup
        $markedUp = $costPrice * (1 + $markupPercentage);

        // 3. Round up to nearest naira step (e.g. 5 or 10)
        $rounded = $roundToNearest > 1
            ? ceil($markedUp / $roundToNearest) * $roundToNearest
            : ceil($markedUp);

        // 4. Hard guard: selling price must never be below cost price
        return (float) max($rounded, $costPrice);
    }

    /**
     * Check if a new cost represents a significant price jump (> threshold).
     *
     * @param  float  $threshold  Fraction (default 0.30 = 30%).
     */
    public function isPriceJump(float $oldCost, float $newCost, float $threshold = 0.30): bool
    {
        if ($oldCost <= 0) {
            return false;
        }

        $changeRatio = abs($newCost - $oldCost) / $oldCost;

        return $changeRatio > $threshold;
    }
}
