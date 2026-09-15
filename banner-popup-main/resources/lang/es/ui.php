<?php

return [
    'model_label' => 'Popup Banner',
    'model_label_plural' => 'Popup Banners',
    'nav_group' => 'Sitio',

    'sections' => [
        'images' => 'Imágenes del banner',
        'activation' => 'Activación',
        'display_rules' => 'Reglas de visualización',
        'publication' => 'Publicación',
    ],

    'descriptions' => [
        'images' => 'Sube imágenes para cada breakpoint. Tablet y móvil recurren al escritorio si no se configuran.',
        'activation' => 'Configura cómo y cuándo se activará este popup.',
        'display_rules' => 'Define dónde y con qué frecuencia aparecerá este popup.',
        'publication' => 'Controla la visibilidad y la programación horaria.',
    ],

    'fields' => [
        'name' => 'Nombre interno',
        'is_active' => 'Activo',
        'trigger' => 'Disparador',
        'trigger_delay' => 'Retraso',
        'link_url' => 'URL del enlace',
        'link_target' => 'Abrir en',
        'show_frequency' => 'Frecuencia de aparición',
        'target_pages' => 'Páginas objetivo',
        'starts_at' => 'Fecha de inicio',
        'ends_at' => 'Fecha de fin',
        'banner_desktop' => 'Escritorio',
        'banner_tablet' => 'Tablet',
        'banner_mobile' => 'Móvil',
    ],

    'helpers' => [
        'banner_desktop' => 'Recomendado: 1200 × 600 px',
        'banner_tablet' => 'Recomendado: 768 × 500 px. Recurrirá al escritorio si no se sube.',
        'banner_mobile' => 'Recomendado: 480 × 640 px. Recurrirá a tablet si no se sube.',
        'target_pages' => 'Deja vacío para mostrar en todas las páginas.',
        'is_active' => 'Desactiva para ocultar temporalmente este popup.',
    ],

    'placeholders' => [
        'target_pages' => 'Todas las páginas',
    ],

    'trigger_options' => [
        'page_load' => 'Al cargar la página',
        'delay' => 'Después de un retraso',
        'exit_intent' => 'Al intentar salir',
    ],

    'link_target_options' => [
        '_self' => 'Misma pestaña',
        '_blank' => 'Nueva pestaña',
    ],

    'frequency_options' => [
        'always' => 'Siempre',
        '1' => 'Una vez al día',
        '3' => 'Cada 3 días',
        '7' => 'Cada 7 días',
        '30' => 'Cada 30 días',
        'once' => 'Solo una vez',
    ],

    'columns' => [
        'currently_active' => 'Vigente',
    ],

    'suffix' => [
        'seconds' => 'seg',
    ],
];
