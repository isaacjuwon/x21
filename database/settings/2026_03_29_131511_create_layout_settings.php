<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('layout.primary_color', '#4f46e5');
        $this->migrator->add('layout.sidebar_collapsible', true);
        $this->migrator->add('layout.font_family', 'Inter');
    }
};
