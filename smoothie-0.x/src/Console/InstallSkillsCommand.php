<?php

declare(strict_types=1);

namespace Aipencil\Smoothie\Console;

use Aipencil\Smoothie\Contracts\SupportSkills;
use Aipencil\Smoothie\Install\CodeEnvironment;
use Aipencil\Smoothie\Install\Skill;
use Aipencil\Smoothie\Install\SkillComposer;
use Aipencil\Smoothie\Install\SkillWriter;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\select;
use function Laravel\Prompts\warning;

final class InstallSkillsCommand extends Command
{
    protected $signature = 'smoothie:skill';

    protected $description = 'Install Smoothie skills';

    private ?CodeEnvironment $selectedEditor = null;

    public function handle(SupportSkills $app): int
    {
        intro('🌊 Smoothie Skills Installer');

        $this->selectedEditor = $this->selectCodeEditor();

        if (! $this->selectedEditor) {
            info('Installation cancelled.');

            return self::SUCCESS;
        }

        $skillComposer = new SkillComposer;
        $availableSkills = $skillComposer->skills();

        if ($availableSkills->isEmpty()) {
            warning('No skills available to install.');

            return self::SUCCESS;
        }

        $installedSkills = $this->getInstalledSkills();

        $skillsToInstall = $this->selectSkills($availableSkills, $installedSkills);

        if ($skillsToInstall->isEmpty()) {
            info('No skills selected for installation.');

            return self::SUCCESS;
        }

        info("Installing skills for {$this->selectedEditor->label}...");
        $writer = new SkillWriter($app, $this->selectedEditor);
        $results = $writer->writeAll($skillsToInstall);
        $this->displayResults($results);

        outro('✓ Smoothie skills installed successfully!');

        return self::SUCCESS;
    }

    protected function selectCodeEditor(): ?CodeEnvironment
    {
        $options = collect(CodeEnvironment::all())
            ->mapWithKeys(fn (CodeEnvironment $env): array => [
                $env->name => $env->label,
            ])
            ->all();

        $selected = select(
            label: 'Which code editor do you use?',
            options: $options,
        );

        return CodeEnvironment::byName($selected);
    }

    /**
     * Get list of already installed skills.
     *
     * @return Collection<string>
     */
    protected function getInstalledSkills(): Collection
    {
        $skillsPath = base_path($this->selectedEditor->skillsPath);

        if (! is_dir($skillsPath)) {
            return collect();
        }

        return collect(array_filter(
            scandir($skillsPath) ?: [],
            fn (string $item): bool => $item !== '.' && $item !== '..' && is_dir($skillsPath.'/'.$item)
        ));
    }

    /**
     * @param  Collection<string, Skill>  $availableSkills
     * @param  Collection<string>  $installedSkills
     * @return Collection<string, Skill>
     */
    protected function selectSkills(Collection $availableSkills, Collection $installedSkills): Collection
    {
        $choices = $availableSkills
            ->mapWithKeys(fn (Skill $skill): array => [
                $skill->name => $this->formatSkillLabel($skill, $installedSkills),
            ])
            ->all();

        if (empty($choices)) {
            return collect();
        }

        $selected = multiselect(
            label: 'Which Filament skills would you like to install?',
            options: $choices,
            hint: 'Use space to select, enter to confirm',
        );

        if (empty($selected)) {
            return collect();
        }

        return $availableSkills->filter(
            fn (Skill $skill): bool => in_array($skill->name, $selected, true)
        );
    }

    /**
     * Format skill label with installed indicator.
     */
    protected function formatSkillLabel(Skill $skill, Collection $installedSkills): string
    {
        $label = $skill->displayName();

        if ($installedSkills->contains($skill->name)) {
            return "{$label} ✓ (already installed)";
        }

        return $label;
    }

    /**
     * @param  array<string, int>  $results
     */
    protected function displayResults(array $results): void
    {
        $messages = [];

        foreach ($results as $skillName => $status) {
            $messages[] = match ($status) {
                SkillWriter::SUCCESS => "  ✓ {$skillName} installed",
                SkillWriter::UPDATED => "  ↻ {$skillName} updated",
                SkillWriter::FAILED => "  ✗ {$skillName} failed",
                default => null,
            };
        }

        if (! empty($messages)) {
            note(implode("\n", array_filter($messages)));
        }
    }
}
