<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('loans.enable_prepayment_penalty', true);
        $this->migrator->add('loans.prepayment_penalty_percentage', 50.0);
    }
};
