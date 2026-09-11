<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Settings\GeneralSettings;
use App\Settings\LayoutSettings;
use App\Settings\LoanSettings;
use App\Settings\ShareSettings;
use App\Settings\WalletSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Settings', 'Application configuration')]
#[Unauthenticated]
class AppSettingsController
{
    #[Response([
        'data' => [
            'general' => [
                'site_name' => 'My App',
                'site_logo' => 'https://example.com/logo.png',
                'site_dark_logo' => null,
                'site_favicon' => null,
                'site_description' => 'A great app.',
                'contact_email' => 'hello@example.com',
                'support_email' => 'support@example.com',
                'currency' => 'NGN',
                'timezone' => 'Africa/Lagos',
                'maintenance_mode' => false,
                'registration_enabled' => true,
            ],
            'wallet' => [
                'min_withdrawal' => 500.00,
                'max_withdrawal' => 500000.00,
                'withdrawal_fee' => 50.00,
            ],
            'shares' => [
                'price_per_share' => 100.00,
                'min_shares_purchase' => 1,
                'max_shares_per_user' => 10000,
                'holding_period_days' => 180,
            ],
            'loans' => [
                'min_amount' => 5000.00,
                'max_amount' => 500000.00,
                'default_interest_rate' => 5.0,
                'interest_method' => 'FlatRate',
                'auto_approve' => false,
                'enforce_kyc_requirement' => true,
                'enforce_shares_requirement' => true,
                'enforce_min_account_age' => true,
                'min_account_age_days' => 90,
            ],
            'layout' => [
                'primary_color' => '#3B82F6',
                'font_family' => 'Inter',
                'about' => null,
                'address' => null,
                'facebook' => null,
                'twitter' => null,
                'instagram' => null,
                'email' => null,
                'homepage_title' => 'Welcome',
                'homepage_description' => 'The best app.',
                'homepage_features_title' => 'Features',
                'homepage_features_description' => 'What we offer.',
                'homepage_features_items' => [],
            ],
        ],
    ], status: 200, description: 'Application settings')]
    public function __invoke(
        Request $request,
        GeneralSettings $general,
        WalletSettings $wallet,
        ShareSettings $shares,
        LoanSettings $loans,
        LayoutSettings $layout,
    ): JsonResponse {
        return response()->json([
            'data' => [
                'general' => [
                    'site_name' => $general->site_name,
                    'site_logo' => $general->site_logo,
                    'site_dark_logo' => $general->site_dark_logo,
                    'site_favicon' => $general->site_favicon,
                    'site_description' => $general->site_description,
                    'contact_email' => $general->contact_email,
                    'support_email' => $general->support_email,
                    'currency' => $general->currency,
                    'timezone' => $general->timezone,
                    'maintenance_mode' => $general->maintenance_mode,
                    'registration_enabled' => $general->registration_enabled,
                ],
                'wallet' => [
                    'min_withdrawal' => $wallet->min_withdrawal,
                    'max_withdrawal' => $wallet->max_withdrawal,
                    'withdrawal_fee' => $wallet->withdrawal_fee,
                ],
                'shares' => [
                    'price_per_share' => $shares->price_per_share,
                    'min_shares_purchase' => $shares->min_shares_purchase,
                    'max_shares_per_user' => $shares->max_shares_per_user,
                    'holding_period_days' => $shares->holding_period_days,
                ],
                'loans' => [
                    'min_amount' => $loans->min_amount,
                    'max_amount' => $loans->max_amount,
                    'default_interest_rate' => $loans->default_interest_rate,
                    'interest_method' => $loans->interest_method->value,
                    'auto_approve' => $loans->auto_approve,
                    'enforce_kyc_requirement' => $loans->enforce_kyc_requirement,
                    'enforce_shares_requirement' => $loans->enforce_shares_requirement,
                    'enforce_min_account_age' => $loans->enforce_min_account_age,
                    'min_account_age_days' => $loans->min_account_age_days,
                ],
                'layout' => [
                    'primary_color' => $layout->primary_color,
                    'font_family' => $layout->font_family,
                    'about' => $layout->about,
                    'address' => $layout->address,
                    'facebook' => $layout->facebook,
                    'twitter' => $layout->twitter,
                    'instagram' => $layout->instagram,
                    'email' => $layout->email,
                    'homepage_title' => $layout->homepage_title,
                    'homepage_description' => $layout->homepage_description,
                    'homepage_features_title' => $layout->homepage_features_title,
                    'homepage_features_description' => $layout->homepage_features_description,
                    'homepage_features_items' => $layout->homepage_features_items,
                ],
            ],
        ]);
    }
}
