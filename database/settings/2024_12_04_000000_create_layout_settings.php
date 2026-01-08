<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('layout.homepage_faq_title', 'Frequently Asked Questions');
        $this->migrator->add('layout.homepage_faq_description', 'Here are some common questions about our platform.');
        $this->migrator->add('layout.homepage_faq_items', []);

        $this->migrator->add('layout.homepage_features_title', 'Our Features');
        $this->migrator->add('layout.homepage_features_description', 'Discover what makes us unique.');
        $this->migrator->add('layout.homepage_features_items', []);

        $this->migrator->add('layout.banner', null);
        $this->migrator->add('layout.about', null);
        $this->migrator->add('layout.address', null);
        $this->migrator->add('layout.facebook', null);
        $this->migrator->add('layout.twitter', null);
        $this->migrator->add('layout.instagram', null);
        $this->migrator->add('layout.email', null);
        $this->migrator->add('layout.homepage_title', 'Frequently Asked Questions');
        $this->migrator->add('layout.homepage_description', 'Here are some common questions about our platform.');
    }
};
