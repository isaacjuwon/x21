<?php

declare(strict_types=1);

namespace Aipencil\Smoothie\Contracts;

use Aipencil\Smoothie\Install\CodeEnvironment;

interface SupportSkills
{
    public function skillsPath(CodeEnvironment $environment): string;
}
