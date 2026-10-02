<?php

namespace Database\Seeders;

use App\Settings\IntegrationSettings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use ReflectionNamedType;

class SettingsSeeder extends Seeder
{
    /**
     * Ensure every declared property on every Settings class has a row
     * in the settings table so spatie/laravel-settings never throws
     * MissingSettings during normal container resolution.
     */
    public function run(): void
    {
        $classes = [
            IntegrationSettings::class,
        ];

        foreach ($classes as $class) {
            if (! method_exists($class, 'group')) {
                continue;
            }

            $reflection = new ReflectionClass($class);
            $group = $class::group();
            $defaults = $this->defaultValuesFor($class);

            foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                if ($property->isStatic()) {
                    continue;
                }

                $name = $property->getName();
                $type = $property->getType();

                if ($type instanceof ReflectionNamedType && ! $type->allowsNull() && ! isset($defaults[$name])) {
                    $defaults[$name] = match ($type->getName()) {
                        'string' => '',
                        'int' => 0,
                        'float' => 0.0,
                        'bool' => false,
                        'array' => [],
                        default => null,
                    };
                }

                $value = array_key_exists($name, $defaults) ? $defaults[$name] : null;

                DB::table('settings')->upsert(
                    values: [
                        'group' => $group,
                        'name' => $name,
                        'locked' => false,
                        'payload' => json_encode($value, JSON_THROW_ON_ERROR),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    uniqueBy: ['group', 'name'],
                    update: [
                        'payload' => DB::raw('payload'),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    /**
     * Sensible defaults for IntegrationSettings so the settings page
     * pre-fills with URLs on fresh installs.
     *
     * @return array<string, mixed>
     */
    protected function defaultValuesFor(string $class): array
    {
        return match ($class) {
            IntegrationSettings::class => [
                'paystack_url' => 'https://api.paystack.co',
                'paystack_public_key' => '',
                'paystack_secret_key' => '',
                'dojah_base_url' => 'https://api.dojah.io',
                'dojah_app_id' => '',
                'dojah_api_key' => '',
                'vtugate_url' => 'https://api.vtugate.com',
                'vtugate_api_key' => '',
                'openai_api_key' => '',
                'openai_model' => 'gpt-4o-mini',
                'kudisms_url' => 'https://api.kudisms.net',
                'kudisms_api_key' => '',
                'kudisms_sender_id' => '',
                'vtpass_url' => 'https://vtpass.com/api',
                'vtpass_api_key' => '',
                'vtpass_secret_key' => '',
                'vtpass_public_key' => '',
            ],
            default => [],
        };
    }
}
