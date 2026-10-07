<?php

declare(strict_types=1);

namespace App\Services\Vtu;

use App\Enums\Plans\ServiceType;

final class PlanCanonicalizer
{
    /**
     * Map of common brand identifiers / aliases to our standard brand slug.
     */
    private const BRAND_MAP = [
        'mtn' => 'mtn',
        'mtn-data' => 'mtn',
        'airtel' => 'airtel',
        'airtel-data' => 'airtel',
        'glo' => 'glo',
        'glo-data' => 'glo',
        'glo-sme-data' => 'glo',
        'globacom' => 'glo',
        '9mobile' => '9mobile',
        'etisalat' => '9mobile',
        'etisalat-data' => '9mobile',
        'dstv' => 'dstv',
        'gotv' => 'gotv',
        'startimes' => 'startimes',
        'showmax' => 'showmax',
        'waec' => 'waec',
        'waec-registration' => 'waec',
        'jamb' => 'jamb',
        'ikeja-electric' => 'ikeja-electric',
        'eko-electric' => 'eko-electric',
        'kano-electric' => 'kano-electric',
        'portharcourt-electric' => 'portharcourt-electric',
        'jos-electric' => 'jos-electric',
        'ibadan-electric' => 'ibadan-electric',
        'kaduna-electric' => 'kaduna-electric',
        'aedc-abuja' => 'aedc-abuja',
        'eedc-enugu' => 'eedc-enugu',
        'bedc-benin' => 'bedc-benin',
        'aba-electric' => 'aba-electric',
        'yedc-yola' => 'yedc-yola',
    ];

    /**
     * Normalize brand name or service ID to a standard brand slug.
     */
    public function normalizeBrand(string $raw): string
    {
        $cleaned = strtolower(trim($raw));
        $cleaned = preg_replace('/\s+/', '-', $cleaned) ?? $cleaned;

        if (isset(self::BRAND_MAP[$cleaned])) {
            return self::BRAND_MAP[$cleaned];
        }

        foreach (self::BRAND_MAP as $alias => $canonical) {
            if (str_contains($cleaned, $alias)) {
                return $canonical;
            }
        }

        return preg_replace('/[^a-z0-9\-]/', '', $cleaned) ?? 'general';
    }

    /**
     * Parse data plan text into structured volume, type, and duration.
     *
     * @return array{volume: string, volume_mb: int, type: string, duration: string, canonical_key_suffix: string}
     */
    public function parseDataPlan(string $name, ?string $rawType = null, ?string $rawDuration = null): array
    {
        $text = strtolower($name.' '.($rawType ?? '').' '.($rawDuration ?? ''));

        // 1. Extract volume
        $volume = '1GB';
        $volumeMb = 1024;
        $volumeSlug = '1gb';

        if (preg_match('/(\d+(?:\.\d+)?)\s*(tb|gb|mb)/i', $name, $matches)) {
            $num = (float) $matches[1];
            $unit = strtoupper($matches[2]);

            // Clean 1.0 to 1
            $displayNum = (fmod($num, 1.0) === 0.0) ? (string) (int) $num : (string) $num;
            $volume = "{$displayNum}{$unit}";
            $volumeSlug = strtolower("{$displayNum}{$unit}");

            $volumeMb = match ($unit) {
                'TB' => (int) ($num * 1024 * 1024),
                'GB' => (int) ($num * 1024),
                'MB' => (int) $num,
                default => 1024,
            };
        }

        // 2. Extract Type (SME, Corporate Gifting, Gifting, Direct)
        $type = 'Direct';
        $typeSlug = 'direct';

        if (preg_match('/\b(sme)\b/i', $text)) {
            $type = 'SME';
            $typeSlug = 'sme';
        } elseif (preg_match('/\b(corporate|cg|corp)\b/i', $text)) {
            $type = 'Corporate Gifting';
            $typeSlug = 'corporate';
        } elseif (preg_match('/\b(gift|gifting|share)\b/i', $text)) {
            $type = 'Gifting';
            $typeSlug = 'gifting';
        } elseif (preg_match('/\b(coupon)\b/i', $text)) {
            $type = 'Coupon';
            $typeSlug = 'coupon';
        }

        // 3. Extract Duration
        $duration = '30 Days';
        $durationSlug = '30days';

        if (preg_match('/(\d+)\s*(hour|hours|hr|hrs)/i', $text, $dMatches)) {
            $hrs = (int) $dMatches[1];
            $duration = "{$hrs} Hours";
            $durationSlug = "{$hrs}hrs";
        } elseif (preg_match('/(\d+)\s*(day|days|d)/i', $text, $dMatches)) {
            $days = (int) $dMatches[1];
            $duration = "{$days} Day".($days > 1 ? 's' : '');
            $durationSlug = "{$days}days";
        } elseif (preg_match('/(\d+)\s*(week|weeks|w)/i', $text, $dMatches)) {
            $wks = (int) $dMatches[1];
            $days = $wks * 7;
            $duration = "{$days} Days";
            $durationSlug = "{$days}days";
        } elseif (preg_match('/(\d+)\s*(month|months|m)/i', $text, $dMatches)) {
            $months = (int) $dMatches[1];
            $days = $months * 30;
            $duration = "{$days} Days";
            $durationSlug = "{$days}days";
        }

        $canonicalKeySuffix = "{$volumeSlug}_{$typeSlug}_{$durationSlug}";

        return [
            'volume' => $volume,
            'volume_mb' => $volumeMb,
            'type' => $type,
            'duration' => $duration,
            'canonical_key_suffix' => $canonicalKeySuffix,
        ];
    }

    /**
     * Compute a canonical key for a data plan.
     */
    public function buildDataCanonicalKey(string $brandSlug, string $volumeSlug, string $typeSlug, string $durationSlug): string
    {
        $brand = $this->normalizeBrand($brandSlug);
        $v = preg_replace('/[^a-z0-9]/', '', strtolower(trim($volumeSlug))) ?? '';
        $t = preg_replace('/[^a-z0-9]/', '', strtolower(trim($typeSlug))) ?? '';
        $d = preg_replace('/[^a-z0-9]/', '', strtolower(trim($durationSlug))) ?? '';

        return "{$brand}_data_{$v}_{$t}_{$d}";
    }

    /**
     * Compute a canonical key for a cable plan / bouquet.
     */
    public function buildCableCanonicalKey(string $brandSlug, string $bouquetName): string
    {
        $brand = $this->normalizeBrand($brandSlug);

        // Normalize bouquet name: strip brand names and common words
        $clean = strtolower($bouquetName);
        $clean = str_replace(['dstv', 'gotv', 'startimes', 'showmax', 'bouquet', 'package'], '', $clean);
        $clean = preg_replace('/[^a-z0-9]+/i', '_', trim($clean)) ?? '';
        $clean = trim($clean, '_');

        return "{$brand}_cable_{$clean}";
    }

    /**
     * Compute a canonical key for an education pin.
     */
    public function buildEducationCanonicalKey(string $brandSlug, string $name): string
    {
        $brand = $this->normalizeBrand($brandSlug);
        $clean = strtolower($name);
        $clean = str_replace(['result', 'checker', 'pin', 'waec', 'jamb', 'direct'], '', $clean);
        $clean = preg_replace('/[^a-z0-9]+/i', '_', trim($clean)) ?? '';
        $clean = trim($clean, '_');

        $suffix = $clean !== '' ? $clean : 'pin';

        return "{$brand}_education_{$suffix}";
    }

    /**
     * Format a clean, unified display name for the plan.
     */
    public function formatUnifiedName(
        string $brandName,
        ServiceType $serviceType,
        string $name,
        ?string $type = null,
        ?string $duration = null,
        ?string $volume = null
    ): string {
        if ($serviceType === ServiceType::Data && $volume !== null) {
            $t = ($type && $type !== 'Direct') ? " {$type}" : '';
            $d = $duration ? " - {$duration}" : '';

            return "{$brandName} {$volume}{$t}{$d}";
        }

        return trim("{$name}");
    }
}
