<p align="center">
    <img src="https://github.com/user-attachments/assets/d8939cb2-9952-4e31-9c80-521788a9fe53" alt="Banner" style="width: 100%; max-width: 800px;" />
</p>


<p align="center">
    <a href="https://github.com/aipencil/smoothie/actions"><img alt="Tests passing" src="https://img.shields.io/badge/Tests-passing-green?style=for-the-badge&logo=github"></a>
    <a href="https://laravel.com"><img alt="Laravel v12" src="https://img.shields.io/badge/Laravel-v12-FF2D20?style=for-the-badge&logo=laravel"></a>
    <a href="https://filamentphp.com"><img alt="Filament v4" src="https://img.shields.io/badge/Filament-v4-0EA5E9?style=for-the-badge"></a>
    <a href="https://php.net"><img alt="PHP 8.3+" src="https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php"></a>
    <a href="https://github.com/aipencil/smoothie"><img alt="Boost-Compatible" src="https://img.shields.io/badge/Boost-Compatible-9333EA?style=for-the-badge"></a>
</p>

Smoothie is a Filament Admin Agent skills installer package built with **Laravel Boost** that extends Filament with reusable features, tools, and UI enhancements to speed up Laravel admin panel development. It automatically installs skills to your AI editor's configuration and updates guideline files so your AI assistant knows how to help you build with Filament.

## Installation

Install the package via Composer:

```bash
composer require aipencil/smoothie --dev
```

Publish the package configuration:

```bash
php artisan smoothie:install
```

This will set up Smoothie and configure your AI editor guidelines.

## Usage

### Installing Skills

Use the skill installation command to browse and install available Filament development skills:

```bash
php artisan smoothie:skill
```

This command will:

1. Show you all available skills
2. Mark already-installed skills with a checkmark
3. Install selected skills to your configured AI editor
4. Automatically update your AI editor's guidelines

### Supported AI Editors

Smoothie automatically detects and works with these AI editors:

- **VS Code** - GitHub Copilot
- **Cursor** - Built-in AI
- **Claude Code** - Claude Desktop
- **PhpStorm** - JetBrains AI Assistant
- **Gemini** - Google Gemini
- **OpenCode** - Web-based editor
- **Codex** - OpenAI Codex

Skills are automatically organized in your editor's configuration directory and guidelines are updated accordingly.

## Testing

Run the test suite:

```bash
composer test
```

This runs:
- **Unit Tests** - Pest test suite (18 tests)
- **Code Style** - Laravel Pint style checks
- **Static Analysis** - PHPStan level 5 analysis

## Changelog

See [CHANGELOG](CHANGELOG.md) for release history and updates.

## Contributing

Found a bug or have a feature request? Please open an issue on [GitHub](https://github.com/aipencil/smoothie/issues).

## Security Vulnerabilities

If you discover a security vulnerability, please email phonelaayy@gmail.com instead of using the issue tracker.

## License

The MIT License (MIT). See [License File](LICENSE.md) for more information.
