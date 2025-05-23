<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DaroryRequests extends Command
{
    protected $signature = 'darory:requests {model?} {--all}';
    protected $description = 'Generate validation requests from model schema using stubs';

    protected $skippedModels = ['Cache','CacheLock','Job','JobBatch','Session'];

    public function handle()
    {

        if (app()->environment('production')) {
            $this->error("This command cannot be executed in production.");
            return;
        }

        if ($this->option('all')) {
            $this->generateForAllModels();
        } else {
            $modelName = $this->argument('model');

            if (!$modelName) {
                $modelName = $this->askUserForModel();
                if (!$modelName) {
                    $this->error("No valid model selected. Exiting...");
                    return;
                }
            }

            $this->generateForModel($modelName);
        }
    }

    protected function askUserForModel()
    {
        $models = $this->getModelsList();

        if ($models->isEmpty()) {
            $this->error("No models found in the app/Models directory.");
            return null;
        }

        // Reset index numbering starting from 1
        $modelsWithIndex = [];
        $modelNames = [];
        $counter = 1;

        foreach ($models as $model) {
            $modelsWithIndex[] = [
                '#' => $counter++, // Incrementing index manually
                'Model' => $model['Model'],
                'Store Request' => $model['Store Request'],
                'Update Request' => $model['Update Request'],
                'Controller' => $model['Controller'], // Include Controller status
            ];
            $modelNames[] = $model['Model']; // Store models for selection
        }

        // Add "All Models" option
        $modelsWithIndex[] = [
            '#' => $counter,
            'Model' => '🚀 All Models',
            'Store Request' => '-',
            'Update Request' => '-',
            'Controller' => '-',
        ];
        $modelNames[] = 'ALL_MODELS';

        // Show table with updated numbering
        $this->table(['#', 'Model', 'Store Request', 'Update Request', 'Controller'], $modelsWithIndex);

        $selectedIndex = (int) $this->ask("Enter the number of the model to generate requests for:");

        if ($selectedIndex < 1 || $selectedIndex > count($modelNames)) {
            $this->error("Invalid selection. Exiting...");
            return null;
        }

        if ($modelNames[$selectedIndex - 1] === 'ALL_MODELS') {
            $this->generateForAllModels();
            return null;
        }

        return $modelNames[$selectedIndex - 1]; // Adjust for array index
    }





    protected function getModelsList()
    {
        $modelPath = app_path('Models');
        return collect(File::files($modelPath))->map(function ($file) {
            $modelName = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            // Skip models in the $skippedModels array
            if (in_array($modelName, $this->skippedModels)) {
                return null;
            }

            return [
                'Model' => $modelName,
                'Store Request' => $this->checkRequestExists("Store{$modelName}Request"),
                'Update Request' => $this->checkRequestExists("Update{$modelName}Request"),
                'Controller' => $this->checkControllerExists("{$modelName}Controller"),
            ];
        })->filter(); // Remove null values (skipped models)
    }

    protected function checkControllerExists($className)
    {
        return File::exists(app_path("Http/Controllers/$className.php")) ? '✅' : '❌';
    }


    protected function checkRequestExists($className)
    {
        return File::exists(app_path("Http/Requests/$className.php")) ? '✅' : '❌';
    }

    protected function generateForAllModels()
    {
        $models = $this->getModelsList();
        foreach ($models as $model) {
            if (!in_array($model['Model'], $this->skippedModels)) {
                $this->generateForModel($model['Model']);
            }
        }
    }

    protected function generateForModel($modelName)
{
    $modelClass = "App\\Models\\$modelName";

    // if (!class_exists($modelClass)) {
    //     $this->error("Model $modelName does not exist.");
    //     return;
    // }

    $storeRequestExists = File::exists(app_path("Http/Requests/Store{$modelName}Request.php"));
    $updateRequestExists = File::exists(app_path("Http/Requests/Update{$modelName}Request.php"));

    // if ($storeRequestExists && $updateRequestExists) {
    //     if (!$this->confirm("Both Store and Update requests for $modelName already exist. Do you want to regenerate them?")) {
    //         $this->info("Skipping request generation for $modelName.");

    //     }
    // } elseif ($storeRequestExists) {
    //     if (!$this->confirm("Store request for $modelName already exists. Do you want to regenerate it?")) {
    //         $this->info("Skipping Store request for $modelName.");
    //         $storeRequestExists = false; // Prevent overwriting
    //     }
    // } elseif ($updateRequestExists) {
    //     if (!$this->confirm("Update request for $modelName already exists. Do you want to regenerate it?")) {
    //         $this->info("Skipping Update request for $modelName.");
    //         $updateRequestExists = false; // Prevent overwriting
    //     }
    // }

    // If both were skipped, exit
    // if (!$storeRequestExists && !$updateRequestExists) {
    //     return;
    // }

    $tableName = (new $modelClass)->getTable();
    $modelInstance = new $modelClass;
    $columns = $modelInstance->getFillable();

    if (empty($columns)) {
        $this->warn("No fillable fields found in $modelName. Skipping request generation.");
        return;
    }

    $validationRules = $this->generateValidationRules($columns, $tableName);
    $validationMessages = $this->generateValidationMessages($validationRules);

    //if (!$storeRequestExists) {
        $this->generateRequest("Store{$modelName}Request", $validationRules, $validationMessages, 'store_request');
    //}

    //if (!$updateRequestExists) {
        $this->generateRequest("Update{$modelName}Request", $validationRules, $validationMessages, 'update_request');
    //}

    $this->info("Validation requests for $modelName generated successfully.");
}


    protected function generateRequest($className, $rules, $messages, $stubFile)
    {
        $requestPath = app_path("Http/Requests/$className.php");

        // Check if the request file already exists
        if (File::exists($requestPath)) {
            if (!$this->confirm("$className already exists. Do you want to overwrite it?")) {
                $this->info("Skipping $className generation.");
                return;
            }
        }

        $stubPath = app_path("Console/Commands/stubs/requests/$stubFile.stub");

        if (!File::exists($stubPath)) {
            $this->error("Stub file not found: $stubFile");
            return;
        }

        $stubContent = File::get($stubPath);
        $stubContent = str_replace(
            ['{{ className }}', '{{ rules }}', '{{ messages }}'],
            [$className, implode(",\n            ", $rules), implode(",\n            ", $messages)],
            $stubContent
        );

        File::put($requestPath, $stubContent);
        $this->info("Created: $className");
    }


    protected function generateValidationRules($columns, $tableName)
    {
        $rules = [];

        foreach ($columns as $column) {
            if (in_array($column, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            $type = DB::getSchemaBuilder()->getColumnType($tableName, $column);
            $rule = "'$column' => '";

            if (Str::contains($column, '_id')) {
                $rule .= "exists:" . Str::plural(Str::replaceLast('_id', '', $column)) . ",id";
            } elseif (Str::contains($type, 'string')) {
                $rule .= "required|string|max:255";
            } elseif (Str::contains($type, 'text')) {
                $rule .= "required|string";
            } elseif (Str::contains($type, 'integer')) {
                $rule .= "required|integer";
            } elseif (Str::contains($type, 'boolean')) {
                $rule .= "required|boolean";
            } elseif (Str::contains($type, 'date')) {
                $rule .= "required|date";
            } else {
                $rule .= "required";
            }

            $rule .= "'";
            $rules[] = $rule;
        }

        return $rules;
    }

    protected function generateValidationMessages($validationRules)
    {
        $messages = [];

        foreach ($validationRules as $rule) {
            preg_match("/'([^']+)' => '([^']+)'/", $rule, $matches);
            $column = $matches[1];
            $rules = explode('|', $matches[2]);
            $fieldName = Str::title(str_replace(['_id', '_'], ['', ' '], $column));

            foreach ($rules as $ruleName) {
                switch ($ruleName) {
                    case 'required':
                        $messages[] = "'$column.required' => __('The $fieldName field is required.')";
                        break;
                    case 'string':
                        $messages[] = "'$column.string' => __('The $fieldName must be a string.')";
                        break;
                    case 'integer':
                        $messages[] = "'$column.integer' => __('The $fieldName must be an integer.')";
                        break;
                    case 'boolean':
                        $messages[] = "'$column.boolean' => __('The $fieldName must be a boolean.')";
                        break;
                    case 'date':
                        $messages[] = "'$column.date' => __('The $fieldName must be a valid date.')";
                        break;
                    case Str::startsWith($ruleName, 'exists:'):
                        $messages[] = "'$column.exists' => __('The selected $fieldName is invalid.')";
                        break;
                }
            }
        }

        return $messages;
    }
}
