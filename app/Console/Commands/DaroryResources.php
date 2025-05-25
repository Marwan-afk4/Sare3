<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

class DaroryResources extends Command
{
    protected $signature = 'darory:resources {request?}';
    protected $description = 'Generate API Resource(s) from Form Request files using a stub template';

    public function handle()
    {
        $requestArg = $this->argument('request');

        if ($requestArg === 'all') {
            $this->generateAllResources();
        } elseif ($requestArg) {
            $this->generateResource($requestArg);
        } else {
            $this->selectAndGenerateResources();
        }
    }

    private function selectAndGenerateResources()
    {
        $requestPath = app_path("Http/Requests");

        if (!File::isDirectory($requestPath)) {
            $this->error("Requests directory not found: {$requestPath}");
            return;
        }

        $files = File::files($requestPath);
        $requestClasses = collect($files)
            ->map(fn($file) => pathinfo($file, PATHINFO_FILENAME))
            ->filter(fn($class) => Str::startsWith($class, 'Store') && Str::endsWith($class, 'Request'))
            ->values()
            ->toArray();

        if (empty($requestClasses)) {
            $this->error("No matching 'Store*Request' files found.");
            return;
        }

        // Add an option to select all
        array_unshift($requestClasses, 'All');

        $selectedRequests = $this->choice(
            "Select the Form Requests to generate resources for (comma-separated, or select 'All')",
            $requestClasses,
            null,
            null,
            true // Allow multiple selections
        );

        if (empty($selectedRequests)) {
            $this->warn("No requests selected. Exiting...");
            return;
        }

        // Handle "All" selection
        if (in_array('All', $selectedRequests)) {
            $selectedRequests = array_slice($requestClasses, 1); // Remove 'All' and select everything else
        }

        foreach ($selectedRequests as $requestClass) {
            $this->generateResource($requestClass);
        }
    }

    private function generateAllResources()
    {
        $requestPath = app_path("Http/Requests");

        if (!File::isDirectory($requestPath)) {
            $this->error("Requests directory not found: {$requestPath}");
            return;
        }

        $files = File::files($requestPath);
        $requestClasses = collect($files)
            ->map(fn($file) => pathinfo($file, PATHINFO_FILENAME))
            ->filter(fn($class) => Str::startsWith($class, 'Store') && Str::endsWith($class, 'Request'))
            ->values();

        if ($requestClasses->isEmpty()) {
            $this->error("No matching 'Store*Request' files found.");
            return;
        }

        $this->info("Generating resources for all matching request files...");
        foreach ($requestClasses as $requestClass) {
            $this->generateResource($requestClass);
        }
    }

    private function generateResource($requestClass)
{
    $requestNamespace = "App\\Http\\Requests\\{$requestClass}";
    $requestPath = app_path("Http/Requests/{$requestClass}.php");

    if (!File::exists($requestPath)) {
        $this->error("Request file not found: {$requestPath}");
        return;
    }

    if (!class_exists($requestNamespace)) {
        require_once $requestPath;
    }

    if (!class_exists($requestNamespace)) {
        $this->error("Class {$requestNamespace} not found.");
        return;
    }

    // Extract validation rules
    $rules = $this->getRulesFromRequest($requestNamespace);

    if (empty($rules)) {
        $this->error("No validation rules found in {$requestNamespace}.");
        return;
    }

    // Define resource name and path
    $modelName = Str::replaceFirst('Store', '', $requestClass);
    $modelName = Str::replaceLast('Request', '', $modelName);
    $resourceName = "{$modelName}Resource";
    $resourcePath = app_path("Http/Resources/{$resourceName}.php");

    // ✅ Ensure the Resources directory exists
    File::ensureDirectoryExists(app_path('Http/Resources'));

    // Check if resource file exists
    if (File::exists($resourcePath)) {
        if (!$this->confirm("Resource file already exists: {$resourcePath}. Do you want to replace it?", false)) {
            $this->info("Skipped creating resource: {$resourceName}");
            return;
        }
    }

    // Load stub file
    $stubPath = app_path('/Console/Commands/stubs/resource.stub');
    if (!File::exists($stubPath)) {
        $this->error("Stub file not found: {$stubPath}");
        return;
    }

    $stubContent = File::get($stubPath);

    // Generate fields array (including relations)
    $fields = collect(['id' => '(string) $this->id'])
        ->merge(collect($rules)->keys()->mapWithKeys(fn ($field) => [
            $field => Str::endsWith($field, '_id') ? "(string) \$this->$field" : "\$this->$field"
        ]));

    // Handle BelongsTo relationships
    $belongsToRelations = $this->getBelongsToRelations($modelName);

    foreach ($belongsToRelations as $relation) {
        $relationClass = Str::studly($relation); // Convert to PascalCase
        $fields[$relation] = "new {$relationClass}Resource(\$this->$relation)";
    }

    if ($this->hasTimestamps($modelName)) {
        $fields['created_at'] = "\$this->created_at";
        $fields['updated_at'] = "\$this->updated_at";
    }

    // Convert fields array to string format
    $fieldsString = collect($fields)
        ->map(fn ($value, $key) => "'".Str::camel($key)."' => $value")
        ->implode(",\n            ");

    // Replace stub placeholders
    $resourceContent = str_replace(
        ['{{ resourceName }}', '{{ fields }}'],
        [$resourceName, $fieldsString],
        $stubContent
    );

    // Write the resource file
    File::put($resourcePath, $resourceContent);

    $this->info("Resource {$resourceName} created successfully.");
}




    private function getRulesFromRequest($requestClass)
    {
        try {
            $reflection = new ReflectionClass($requestClass);

            if (!$reflection->hasMethod('rules')) {
                return [];
            }

            $method = $reflection->getMethod('rules');

            if (!$method->isPublic() || $method->isStatic()) {
                return [];
            }

            // Invoke the method without creating an instance
            return $method->invoke($reflection->newInstanceWithoutConstructor());
        } catch (\Throwable $e) {
            $this->error("Failed to retrieve rules: " . $e->getMessage());
            return [];
        }
    }

    protected function getBelongsToRelations($modelName)
    {
        $modelPath = app_path("Models/{$modelName}.php");

        if (!file_exists($modelPath)) {
            return [];
        }

        $content = file_get_contents($modelPath);
        preg_match_all('/function\s+(\w+)\s*\(\)\s*{[^}]+?->belongsTo\(([^)]+)\)/', $content, $matches, PREG_SET_ORDER);

        return array_map(fn($match) => trim($match[1]), $matches);
    }

    private function hasTimestamps($modelName)
    {
        $modelClass = "App\\Models\\{$modelName}";

        if (!class_exists($modelClass)) {
            return false;
        }

        try {
            $modelInstance = app($modelClass); // Resolve model instance
            return $modelInstance->timestamps; // Directly check property
        } catch (\Throwable $e) {
            return false;
        }
    }

}
