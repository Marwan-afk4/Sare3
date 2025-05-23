<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DaroryCleanTranslations extends Command
{
    protected $signature = 'darory:cleanup-translations';
    protected $description = 'Remove translated words that are no longer used in any files';

    private $foundTranslations = [];

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

        // Paths for translation files
        $languages = ['en', 'ar']; // Add more languages if needed

        $translationFiles = [];
        $metaFiles = [];

        foreach ($languages as $lang) {
            $translationFiles[$lang] = resource_path("lang/{$lang}.json");
            $metaFiles[$lang] = resource_path("lang/{$lang}_meta.json");
        }

        // Scan all directories for used translations
        foreach ($directories as $dir) {
            $fullPath = base_path($dir);
            if (File::exists($fullPath)) {
                $this->scanDirectory($fullPath);
            }
        }

        foreach ($languages as $lang) {
            $this->cleanupLanguage($translationFiles[$lang], $metaFiles[$lang], $lang);
        }

        $this->info("✅ Cleanup complete! Unused translations removed.");
    }

    private function cleanupLanguage($translationPath, $metaPath, $lang)
    {
        if (!File::exists($translationPath)) {
            $this->info("❌ No translations found in lang/{$lang}.json.");
            return;
        }

        // Load existing translations
        $existingTranslations = json_decode(File::get($translationPath), true) ?? [];
        $existingMeta = File::exists($metaPath) ? json_decode(File::get($metaPath), true) : [];

        // Find unused translations (keys in lang.json that are not found in any file)
        $unusedTranslations = array_diff(array_keys($existingTranslations), $this->foundTranslations);

        if (empty($unusedTranslations)) {
            $this->info("✅ No unused translations found in lang/{$lang}.json.");
            return;
        }

        // Remove unused keys from lang.json
        foreach ($unusedTranslations as $key) {
            unset($existingTranslations[$key]);
            unset($existingMeta[$key]);
        }

        // Save updated files
        File::put($translationPath, json_encode($existingTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        File::put($metaPath, json_encode($existingMeta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("🗑️ Removed " . count($unusedTranslations) . " unused translations from lang/{$lang}.json.");
    }

    private function scanDirectory($directory)
    {
        $files = File::allFiles($directory);

        foreach ($files as $file) {
            $content = File::get($file);

            // Match translation functions like __('key'), @lang('key'), and trans('key')
            preg_match_all("/__\(['\"](.*?)['\"]\)|@lang\(['\"](.*?)['\"]\)|trans\(['\"](.*?)['\"]\)/", $content, $matches);

            foreach (array_merge($matches[1], $matches[2], $matches[3]) as $match) {
                if (!empty($match)) {
                    $this->foundTranslations[] = $match;
                }
            }
        }
    }
}
