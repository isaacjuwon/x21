<?php

declare(strict_types=1);

namespace Aipencil\Smoothie\Install;

abstract class CodeEnvironment
{
    public function __construct(
        public readonly string $name,
        public readonly string $label,
        public readonly string $skillsPath
    ) {}

    abstract public function guidelinesPath(): string;

    final public static function all(): array
    {
        return [
            new VSCode,
            new PhpStorm,
            new Cursor,
            new ClaudeCode,
            new Codex,
            new Gemini,
            new OpenCode,
        ];
    }

    final public static function byName(string $name): ?self
    {
        foreach (self::all() as $env) {
            if ($env->name === $name) {
                return $env;
            }
        }

        return null;
    }

    public function supportsInstructionFiles(): bool
    {
        return true;
    }
}

final class VSCode extends CodeEnvironment
{
    public function __construct()
    {
        parent::__construct(
            name: 'vscode',
            label: 'VS Code / GitHub Copilot',
            skillsPath: '.github/skills/filament-development'
        );
    }

    public function guidelinesPath(): string
    {
        return '.github/copilot-instructions.md';
    }
}

final class PhpStorm extends CodeEnvironment
{
    public function __construct()
    {
        parent::__construct(
            name: 'phpstorm',
            label: 'PhpStorm',
            skillsPath: '.junie/skills/filament-development'
        );
    }

    public function guidelinesPath(): string
    {
        return '.junie/guidelines.md';
    }
}

final class Cursor extends CodeEnvironment
{
    public function __construct()
    {
        parent::__construct(
            name: 'cursor',
            label: 'Cursor',
            skillsPath: '.cursor/skills/filament-development'
        );
    }

    public function guidelinesPath(): string
    {
        return '.cursor/rules/filament.md';
    }
}

final class ClaudeCode extends CodeEnvironment
{
    public function __construct()
    {
        parent::__construct(
            name: 'claude_code',
            label: 'Claude Code',
            skillsPath: '.claude/skills/filament-development'
        );
    }

    public function guidelinesPath(): string
    {
        return 'CLAUDE.md';
    }

    public function supportsInstructionFiles(): bool
    {
        return false;
    }
}

final class Codex extends CodeEnvironment
{
    public function __construct()
    {
        parent::__construct(
            name: 'codex',
            label: 'Codex',
            skillsPath: '.codex/skills/filament-development'
        );
    }

    public function guidelinesPath(): string
    {
        return 'AGENTS.md';
    }
}

final class Gemini extends CodeEnvironment
{
    public function __construct()
    {
        parent::__construct(
            name: 'gemini',
            label: 'Gemini',
            skillsPath: '.gemini/skills/filament-development'
        );
    }

    public function guidelinesPath(): string
    {
        return 'GEMINI.md';
    }
}

final class OpenCode extends CodeEnvironment
{
    public function __construct()
    {
        parent::__construct(
            name: 'opencode',
            label: 'OpenCode',
            skillsPath: '.opencode/skills/filament-development'
        );
    }

    public function guidelinesPath(): string
    {
        return 'AGENTS.md';
    }
}
