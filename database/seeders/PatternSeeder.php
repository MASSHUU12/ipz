<?php

namespace Database\Seeders;

use App\Chatbot\ModuleLoader;
use App\Models\Pattern;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PatternSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder imports patterns from the JSON file and PHP modules
     * into the 'patterns' database table.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->error('Refusing to run seeder in production environment.');
            return;
        }

        DB::table('patterns')->truncate();

        $jsonPatterns = [];
        if (Storage::disk('local')->exists('chatbot_patterns.json')) {
            try {
                $json = Storage::disk('local')->get('chatbot_patterns.json');
                $decoded = json_decode($json, true);
                if (is_array($decoded)) {
                    $jsonPatterns = $decoded;
                }
            } catch (Throwable $e) {
                $this->command->error('Could not read or decode chatbot_patterns.json: ' . $e->getMessage());
            }
        }
        $this->command->info('Found ' . count($jsonPatterns) . ' patterns in the JSON file.');

        $modulePatterns = [];
        try {
            $moduleData = ModuleLoader::loadModules();
            $modulePatterns = $moduleData['patterns'] ?? [];
        } catch (Throwable $e) {
            $this->command->error('Could not load patterns from modules: ' . $e->getMessage());
        }
        $this->command->info('Found ' . count($modulePatterns) . ' patterns in PHP modules.');


        $allPatterns = array_merge($jsonPatterns, $modulePatterns);
        if (empty($allPatterns)) {
            $this->command->warn('No patterns found to seed. Exiting.');
            return;
        }

        $this->command->info('Total patterns to process: ' . count($allPatterns));

        $allowedAccessLevels = [
            'public',
            'authenticated',
            'super_admin',
        ];

        $createdCount = 0;
        foreach ($allPatterns as $p) {
            if (!is_array($p) || empty($p['pattern'])) {
                continue;
            }

            $callback = $p['callback'] ?? null;
            if (is_array($callback) && isset($callback[0], $callback[1])) {
                $callback = $callback[0] . '::' . $callback[1];
            } elseif (!is_string($callback)) {
                $callback = null;
            }

            $responses = $p['responses'] ?? ($p['response'] ?? []);
            if (is_string($responses)) {
                $responses = [$responses];
            } elseif (!is_array($responses)) {
                $responses = [];
            } else {
                if (array_keys($responses) === range(0, count($responses) - 1)) {
                    $responses = array_values(array_filter($responses, fn($v) => is_string($v)));
                } else {
                    foreach ($responses as $localeKey => $val) {
                        if (is_string($val)) {
                        } elseif (is_array($val)) {
                            $responses[$localeKey] = array_values(array_filter($val, fn($v) => is_string($v)));
                        } else {
                            unset($responses[$localeKey]);
                        }
                    }
                }
            }

            $patternField = $p['pattern'];

            $payload = $p['payload'] ?? null;
            if ($payload !== null && !is_array($payload)) {
                if (is_string($payload)) {
                    $decoded = json_decode($payload, true);
                    if (is_array($decoded)) {
                        $payload = $decoded;
                    } else {
                        $this->command->warn('Ignoring non-array payload for pattern: ' . (is_string($patternField) ? $patternField : 'array/...'));
                        $payload = null;
                    }
                } else {
                    $payload = null;
                }
            }

            $providedAccess = null;
            if (isset($p['access_level'])) {
                $providedAccess = $p['access_level'];
            } elseif (isset($p['access'])) {
                $providedAccess = $p['access'];
            }

            $accessLevel = 'public';
            if (is_string($providedAccess) && $providedAccess !== '') {
                $normalized = strtolower(trim($providedAccess));
                if (in_array($normalized, $allowedAccessLevels, true)) {
                    $accessLevel = $normalized;
                } else {
                    $patternPreview = is_string($patternField) ? $patternField : (is_array($patternField) ? json_encode(array_slice($patternField, 0, 1)) : '');
                    $this->command->warn(sprintf(
                        "Pattern '%s' provided invalid access level '%s' — defaulting to '%s'. Allowed: %s",
                        $patternPreview,
                        $providedAccess,
                        $accessLevel,
                        implode(', ', $allowedAccessLevels)
                        ));
                }
            }

            $createData = [
                'pattern' => $patternField,
                'responses' => $responses,
                'callback' => $callback,
                'severity' => $p['severity'] ?? 'low',
                'priority' => (int) ($p['priority'] ?? 0),
                'enabled' => (bool) ($p['enabled'] ?? true),
                'description' => $p['description'] ?? null,
                'group' => $p['group'] ?? null,
                'access_level' => $accessLevel,
                'payload' => $payload,
                'stop_processing' => isset($p['stop_processing']) ? (bool) $p['stop_processing'] : (bool) ($p['stop'] ?? true),
            ];

            Pattern::create($createData);
            $createdCount++;
        }

        $this->command->info("Successfully seeded {$createdCount} patterns into the database.");
    }
}
