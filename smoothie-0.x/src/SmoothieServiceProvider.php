<?php

declare(strict_types=1);

namespace Aipencil\Smoothie;

use Aipencil\Smoothie\Console\InstallCommand;
use Aipencil\Smoothie\Console\InstallSkillsCommand;
use Aipencil\Smoothie\Contracts\SupportSkills;
use Aipencil\Smoothie\Install\CodeEnvironment;
use Illuminate\Support\ServiceProvider;

final class SmoothieServiceProvider extends ServiceProvider implements SupportSkills
{
    public function register(): void
    {
        $this->app->singleton(SupportSkills::class, fn () => $this);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                InstallSkillsCommand::class,
            ]);
        }
    }

    public function skillsPath(CodeEnvironment $environment): string
    {
        return $environment->skillsPath;
    }
}
