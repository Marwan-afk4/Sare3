<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DaroryTranslations extends Command
{
    protected $signature = 'darory:translations';
    protected $description = 'Collect all translatable words from controllers, views, and Blade files and generate a JSON file.';

    private $foundLocations = [];

    public function handle()
    {
        $directories = [
            'resources/views',   // Blade views
            'app/Http/Controllers', // Controllers
            'app/Http/Requests', // Form Requests
            'app/Http/Middleware', // Middlewares
            'app/Models', // Models
            'app/Services', // Services
            'app/Helpers', // Helpers
            'app/Notifications', // Notifications
            'app/Mail', // Mail classes
            'app/Exceptions', // Exceptions
            'app/Http/Livewire', // Livewire components
            'app/Http/Resources', // API Resources
            'app/Enums',
            'routes',
            'config', // Config files
        ];

        $translations = [];

        foreach ($directories as $dir) {
            $fullPath = base_path($dir);
            if (File::exists($fullPath)) {
                $this->scanDirectory($fullPath, $translations);
            }
        }

        // Remove duplicates
        $translations = array_unique($translations);

        // Define file paths
        $langDir = resource_path('lang');
        $localeDir = resource_path('lang/ar');
        $outputPath = resource_path('lang/ar.json');
        $metaOutputPath = resource_path('lang/ar_meta.json');

        // Ensure required directories exist
        File::ensureDirectoryExists($langDir);
        File::ensureDirectoryExists($localeDir);

        // Load existing translations
        $existingTranslations = File::exists($outputPath) ? json_decode(File::get($outputPath), true) : [];
        $existingMeta = File::exists($metaOutputPath) ? json_decode(File::get($metaOutputPath), true) : [];

        // Get only new translations
        $newTranslations = array_diff($translations, array_keys($existingTranslations));

        // Maintain old order and append new ones
        $finalTranslations = $existingTranslations;
        foreach ($newTranslations as $key) {
            $finalTranslations[$key] = '';
        }

        // Merge metadata (file locations)
        $finalMeta = $existingMeta;
        foreach ($translations as $key) {
            $finalMeta[$key] = array_unique($this->foundLocations[$key] ?? []);
        }

        // Save to JSON files
        File::put($outputPath, json_encode($finalTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        File::put($metaOutputPath, json_encode($finalMeta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("✅ Translations collected successfully! Saved to lang/ar.json");
        $this->info("📝 Metadata saved in lang/ar_meta.json with file locations.");
    }

    private function scanDirectory($directory, &$translations)
    {
        $files = File::allFiles($directory);

        foreach ($files as $file) {
            $content = File::get($file);
            $filePath = str_replace(base_path() . '/', '', $file->getRealPath()); // Relative path

            // Match translation functions like __('key'), @lang('key'), and trans('key')
            preg_match_all("/__\(['\"](.*?)['\"]\)|@lang\(['\"](.*?)['\"]\)|trans\(['\"](.*?)['\"]\)/", $content, $matches);

            foreach (array_merge($matches[1], $matches[2], $matches[3]) as $match) {
                if (!empty($match)) {
                    $translations[] = $match;
                    $this->foundLocations[$match][] = $filePath; // Track where it's found
                }
            }
        }
    }
}
