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
            }

            Pattern::create([
                'pattern' => $p['pattern'],
                'responses' => $responses,
                'callback' => $callback,
                'severity' => $p['severity'] ?? 'low',
                'priority' => (int) ($p['priority'] ?? 0),
                'enabled' => (bool) ($p['enabled'] ?? true),
                'description' => $p['description'] ?? null,
                'group' => $p['group'] ?? null,
            ]);
            $createdCount++;
        }

        $this->command->info("Successfully seeded {$createdCount} patterns into the database.");
    }
}
