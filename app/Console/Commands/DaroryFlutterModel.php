<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

class DaroryFlutterModel extends Command
{
    protected $signature = 'darory:flutter-model {model?} {--all}';

    protected $description = 'Generate a Flutter model from a Laravel API Resource and detect related models';

    public function handle()
    {
        if ($this->option('all')) {
            $this->generateAllModels();
            return;
        }

        $modelName = $this->argument('model');

        if (!$modelName) {
            $this->error("Please provide a model name or use --all to generate all models.");
            return;
        }

        $this->generateModel($modelName);
    }

    private function generateAllModels()
    {
        $resourcePath = app_path('Http/Resources');
        if (!File::exists($resourcePath)) {
            $this->error("Resources directory not found.");
            return;
        }

        $resourceFiles = File::files($resourcePath);
        foreach ($resourceFiles as $file) {
            $modelName = str_replace('Resource.php', '', $file->getFilename());
            $this->generateModel($modelName);
        }

        $this->info("All Flutter models generated successfully.");
    }

    private function generateModel($modelName)
    {
        $resourceClass = "App\\Http\\Resources\\{$modelName}Resource";
        $requestClass = "App\\Http\\Requests\\Store{$modelName}Request";

        if (!class_exists($resourceClass)) {
            $this->error("Resource {$resourceClass} not found.");
            return;
        }

        [$attributes, $relatedModels] = $this->getAttributesAndRelationsFromResource($resourceClass);
        $nullableFields = class_exists($requestClass) ? $this->getNullableFieldsFromRequest($requestClass) : [];

        $this->generateFlutterModel($modelName, $attributes, $nullableFields, $relatedModels);
        $this->info("Flutter model for {$modelName} generated successfully.");
    }

    private function getAttributesAndRelationsFromResource($resourceClass)
    {
        $resourceInstance = new $resourceClass(request());
        $resourceArray = $resourceInstance->toArray(request());

        $attributes = [];
        $relatedModels = [];

        foreach ($resourceArray as $key => $value) {
            // Check if the value is an instance of a Resource
            if (is_object($value) && preg_match('/^App\\\\Http\\\\Resources\\\\(\w+)Resource$/', get_class($value), $matches)) {
                $relatedModel = $matches[1]; // Extract related model name
                $relatedModels[$key] = $relatedModel;
                $attributes[] = $key;
            } elseif (is_array($value)) {
                // Handle potential collections as related models
                $relatedModels[$key] = Str::studly(Str::singular($key));
                $attributes[] = $key;
            } else {
                $attributes[] = $key;
            }
        }

        return [$attributes, $relatedModels];
    }


    private function getNullableFieldsFromRequest($requestClass)
{
    $requestInstance = new $requestClass();
    $rules = method_exists($requestInstance, 'rules') ? $requestInstance->rules() : [];

    $fields = [];

    foreach ($rules as $field => $ruleSet) {
        $rulesArray = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;

        // Determine if the field is nullable
        $nullable = !in_array('required', $rulesArray);

        // Determine the field type based on validation rules
        $type = $this->getTypeFromRules($rulesArray);

        $fields[$field] = [
            'nullable' => $nullable,
            'type' => $type,
        ];
    }

    return $fields;
}

/**
 * Get the data type from validation rules.
 *
 * @param array $rules
 * @return string
 */
private function getTypeFromRules(array $rules)
{
    if (in_array('integer', $rules)) {
        return 'int';
    }
    if (in_array('numeric', $rules) || in_array('double', $rules)) {
        return 'double';
    }
    if (in_array('boolean', $rules)) {
        return 'bool';
    }
    if (in_array('array', $rules)) {
        return 'List';
    }
    if (in_array('date', $rules)) {
        return 'DateTime';
    }
    return 'String'; // Default to String
}

private function generateFlutterModel($modelName, $attributes, $nullableFields, $relatedModels)
{
    $stub = File::get(app_path('/Console/Commands/stubs/flutter_model.stub'));

    // Flutter class name (same as Laravel model name)
    $className = Str::studly($modelName);

    // Generate imports for related models
    $imports = '';
    foreach ($relatedModels as $related) {
        $imports .= "import '".Str::snake($related).".dart';\n";
    }

    // Define fields with correct nullable types
    $fields = collect($attributes)->map(function ($attr) use ($nullableFields, $relatedModels) {
        if (isset($relatedModels[$attr])) {
            return "  final ${relatedModels[$attr]}? $attr;";
        }

        // Get field type and nullability
        $fieldInfo = $nullableFields[$attr] ?? ['type' => 'String', 'nullable' => false];
        $type = $fieldInfo['type'];
        $nullable = $fieldInfo['nullable'];

        // Append '?' if the field is nullable
        $typeWithNullability = $nullable ? "$type?" : $type;

        return "  final $typeWithNullability ".Str::camel($attr).";";
    })->implode("\n");

    // Constructor parameters
    $constructorParams = collect($attributes)->map(function ($attr) use ($nullableFields, $relatedModels) {
        if (isset($relatedModels[$attr])) {
            return "    this.$attr,";
        }

        // Get field nullability
        $nullable = $nullableFields[$attr]['nullable'] ?? false;

        // Add 'required' if the field is not nullable
        return $nullable ? "    this.".Str::camel($attr)."," : "    required this.".Str::camel($attr).",";
    })->implode("\n");

    // JSON deserialization
    $fromJson = collect($attributes)->map(function ($attr) use ($nullableFields, $relatedModels) {
        if (isset($relatedModels[$attr])) {
            return "      $attr: ${relatedModels[$attr]}.fromJson(json['$attr']),";
        }

        // Get field type and nullability
        $fieldInfo = $nullableFields[$attr] ?? ['type' => 'String', 'nullable' => false];
        $type = $fieldInfo['type'];
        $nullable = $fieldInfo['nullable'];

        // Handle type conversion in JSON deserialization
        switch ($type) {
            case 'int':
                return "      ".Str::camel($attr).": json['".Str::camel($attr)."'] as int,";
            case 'double':
                return "      ".Str::camel($attr).": (json['".Str::camel($attr)."'] as num).toDouble(),";
            case 'bool':
                return "      ".Str::camel($attr).": json['".Str::camel($attr)."'] as bool,";
            case 'List':
                return "      ".Str::camel($attr).": List<dynamic>.from(json['".Str::camel($attr)."']),";
            case 'DateTime':
                return "      ".Str::camel($attr).": DateTime.parse(json['".Str::camel($attr)."']),";
            default:
                return "      ".Str::camel($attr).": json['".Str::camel($attr)."'] as String,";
        }
    })->implode("\n");

    // JSON serialization
    $toJson = collect($attributes)->map(function ($attr) use ($relatedModels) {
        if (isset($relatedModels[$attr])) {
            return "      '$attr': $attr?.toJson(),";
        }
        return "      '".Str::camel($attr)."': ".Str::camel($attr).",";
    })->implode("\n");

    // Replace placeholders in stub
    $model = str_replace(
        ['{{ className }}', '{{ imports }}', '{{ fields }}', '{{ constructorParams }}', '{{ fromJson }}', '{{ toJson }}'],
        [$className, $imports, $fields, $constructorParams, $fromJson, $toJson],
        $stub
    );

    // Output directory
    $outputDir = base_path("flutter_models");
    if (!File::exists($outputDir)) {
        File::makeDirectory($outputDir, 0755, true);
    }

    // Save the file
    File::put("{$outputDir}/".Str::snake($modelName).".dart", $model);
}
}
