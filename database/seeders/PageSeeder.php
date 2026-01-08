<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about',
                'content' => '<h1>About Us</h1><p>Welcome to our application. We are dedicated to providing the best service possible.</p>',
                'is_published' => true,
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms',
                'content' => '<h1>Terms of Service</h1><p>Please read these terms carefully before using our service.</p>',
                'is_published' => true,
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy',
                'content' => '<h1>Privacy Policy</h1><p>Your privacy is important to us. This policy explains how we handle your data.</p>',
                'is_published' => true,
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }
    }
}
