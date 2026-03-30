<?php

declare(strict_types=1);

namespace Aipencil\Smoothie\Install\Concerns;

use Exception;
use Illuminate\Support\Facades\Blade;

trait RendersBladeGuidelines
{
    protected function renderBladeFile(string $path): string
    {
        $content = file_get_contents($path);

        try {
            return Blade::render($content);
        } catch (Exception $e) {
            return $content;
        }
    }
}
