<?php

return [
    'model_label' => 'Popup Banner',
    'model_label_plural' => 'Popup Banners',
    'nav_group' => 'Site',

    'sections' => [
        'images' => 'Banner Images',
        'activation' => 'Activation',
        'display_rules' => 'Display Rules',
        'publication' => 'Publication',
    ],

    'descriptions' => [
        'images' => 'Upload images for each breakpoint. Mobile and tablet fall back to desktop if not set.',
        'activation' => 'Configure when and how this popup is triggered.',
        'display_rules' => 'Define where and how often this popup appears.',
        'publication' => 'Control visibility and scheduling.',
    ],

    'fields' => [
        'name' => 'Internal name',
        'is_active' => 'Active',
        'trigger' => 'Trigger',
        'trigger_delay' => 'Delay',
        'link_url' => 'Link URL',
        'link_target' => 'Open in',
        'show_frequency' => 'Show frequency',
        'target_pages' => 'Target pages',
        'starts_at' => 'Start date',
        'ends_at' => 'End date',
        'banner_desktop' => 'Desktop',
        'banner_tablet' => 'Tablet',
        'banner_mobile' => 'Mobile',
    ],

    'helpers' => [
        'banner_desktop' => 'Recommended: 1200 × 600 px',
        'banner_tablet' => 'Recommended: 768 × 500 px. Falls back to desktop.',
        'banner_mobile' => 'Recommended: 480 × 640 px. Falls back to tablet.',
        'target_pages' => 'Leave empty to show on all pages.',
        'is_active' => 'Disable to temporarily hide this popup.',
    ],

    'placeholders' => [
        'target_pages' => 'All pages',
    ],

    'trigger_options' => [
        'page_load' => 'On page load',
        'delay' => 'After delay',
        'exit_intent' => 'Exit intent',
    ],

    'link_target_options' => [
        '_self' => 'Same tab',
        '_blank' => 'New tab',
    ],

    'frequency_options' => [
        'always' => 'Always',
        '1' => 'Once per day',
        '3' => 'Every 3 days',
        '7' => 'Every 7 days',
        '30' => 'Every 30 days',
        'once' => 'Only once',
    ],

    'columns' => [
        'currently_active' => 'Live',
    ],

    'suffix' => [
        'seconds' => 'sec',
    ],
];
