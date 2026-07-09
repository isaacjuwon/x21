<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SmsSettings extends Settings
{
    // Loans
    public bool $sms_loan_approved;

    public bool $sms_loan_disbursed;

    public bool $sms_loan_rejected;

    public bool $sms_loan_settled;

    // Shares
    public bool $sms_share_order_approved;

    public bool $sms_share_order_rejected;

    public bool $sms_dividend_paid;

    // Wallets
    public bool $sms_transaction_reversed;

    // Services (VTU)
    public bool $sms_service_purchased;

    // Tickets
    public bool $sms_ticket_created;

    public static function group(): string
    {
        return 'sms_notifications';
    }
}
