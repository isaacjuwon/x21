<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('sms_notifications.sms_loan_approved', false);
        $this->migrator->add('sms_notifications.sms_loan_disbursed', false);
        $this->migrator->add('sms_notifications.sms_loan_rejected', false);
        $this->migrator->add('sms_notifications.sms_loan_settled', false);
        $this->migrator->add('sms_notifications.sms_share_order_approved', false);
        $this->migrator->add('sms_notifications.sms_share_order_rejected', false);
        $this->migrator->add('sms_notifications.sms_dividend_paid', false);
        $this->migrator->add('sms_notifications.sms_transaction_reversed', false);
        $this->migrator->add('sms_notifications.sms_wallet_withdrawn', false);
        $this->migrator->add('sms_notifications.sms_service_purchased', false);
        $this->migrator->add('sms_notifications.sms_ticket_created', false);
    }
};
