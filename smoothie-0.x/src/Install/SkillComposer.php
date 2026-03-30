<?php

declare(strict_types=1);

namespace Aipencil\Smoothie\Install;

use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;

final class SkillComposer
{
    /** @var Collection<string, Skill>|null */
    private ?Collection $skills = null;

    public function resetSkills(): self
    {
        $this->skills = null;

        return $this;
    }

    /**
     * Get all discovered skills (Smoothie built-in and user).
     *
     * @return Collection<string, Skill>
     */
    public function skills(): Collection
    {
        if ($this->skills instanceof Collection) {
            return $this->skills;
        }

        return $this->skills = collect()
            ->merge($this->getSmoothieSkills())
            ->merge($this->getUserSkills());
    }

    /**
     * @return Collection<string, Skill>
     */
    private function getSmoothieSkills(): Collection
    {
        return $this->discoverSkillsFromDirectory(
            $this->getSmoothieAiPath(),
            'smoothie'
        );
    }

    /**
     * @return Collection<string, Skill>
     */
    private function getUserSkills(): Collection
    {
        $userSkillsPath = base_path('.ai/smoothie');

        if (! is_dir($userSkillsPath)) {
            return collect();
        }

        return $this->discoverSkillsFromDirectory($userSkillsPath, 'user')
            ->map(fn (Skill $skill) => $skill->withCustom(true));
    }

    /**
     * @return Collection<string, Skill>
     */
    private function discoverSkillsFromDirectory(string $path, string $package): Collection
    {
        if (! is_dir($path)) {
            return collect();
        }

        $finder = Finder::create()
            ->directories()
            ->in($path)
            ->depth('== 0')
            ->sortByName();

        $skills = collect();

        foreach ($finder as $dir) {
            $name = $dir->getBasename();

            // Skip hidden directories
            if (str_starts_with($name, '.')) {
                continue;
            }

            // Check if SKILL.blade.php exists
            $skillFilePath = $dir->getRealPath().'/SKILL.blade.php';
            if (! is_file($skillFilePath)) {
                continue;
            }

            // Extract description from SKILL.blade.php frontmatter
            $description = $this->extractDescriptionFromSkillFile($skillFilePath);

            $skills->put($name, new Skill(
                name: $name,
                package: $package,
                path: $dir->getRealPath(),
                description: $description,
            ));
        }

        return $skills;
    }

    private function extractDescriptionFromSkillFile(string $filePath): string
    {
        $content = file_get_contents($filePath);

        // Extract description from frontmatter
        if (preg_match('/^---\s*\n.*?description:\s*>-\s*\n\s*(.+?)(?:\n---|\nname:)/s', $content, $matches)) {
            return mb_trim($matches[1]);
        }

        return 'A Smoothie Filament skill';
    }

    private function getSmoothieAiPath(): string
    {
        // Get the path to the smoothie package's skills directory
        return dirname(__DIR__, 2).'/.ai/filament/4/skills';
    }
}
