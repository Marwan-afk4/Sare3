<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DaroryApiControllers extends Command
{
    protected $signature = 'darory:controllers-api {model?} {--all} {--missing}';
    protected $description = 'Generate Laravel controllers Api based on existing models';

    public function handle()
    {

        if (app()->environment('production')) {
            $this->error("This command cannot be executed in production.");
            return;
        }
        
        $this->info("✨ Darory Controllers Api Generator ✨");

        $modelName = $this->argument('model');

        if ($modelName) {
            $this->generateController($modelName);
            return;
        }

        if ($this->option('all')) {
            $this->generateAllControllers();
            return;
        }

        $models = $this->getAllModels();

        if (empty($models)) {
            $this->error("❌ No models found in the `app/Models` directory.");
            return;
        }

        $modelData = [];
        foreach ($models as $index => $model) {
            $modelData[] = [
                '#'          => $index + 1,
                'Model'      => $model,
                'Controller' => $this->fileStatus("Http/Controllers/Api/{$model}Controller"),
                'Store Req'  => $this->fileStatus("Http/Requests/{$model}StoreRequest"),
                'Update Req' => $this->fileStatus("Http/Requests/{$model}UpdateRequest"),
            ];
        }

        $this->table(['#', 'Model', 'Controller', 'Store Req', 'Update Req'], $modelData);

        if ($this->option('missing')) {
            // Auto-generate controllers for missing ones only
            $missingModels = array_filter($models, function ($model) {
                return !File::exists(app_path("Http/Controllers/Api/{$model}Controller.php"));
            });

            if (empty($missingModels)) {
                $this->info("✅ All controllers api already exist.");
                return;
            }

            foreach ($missingModels as $model) {
                $this->generateController($model);
            }

            return;
        }

        $selectedIndexes = $this->ask("Enter model numbers to generate controllers (comma-separated), '0' for all, or 'm' for missing:");

        if ($selectedIndexes === '0') {
            $this->generateAllControllers();
            return;
        }

        if ($selectedIndexes === 'm') {
            $this->call('darory:controllers-api', ['--missing' => true]);
            return;
        }

        $selectedModels = array_map('trim', explode(',', $selectedIndexes));
        foreach ($selectedModels as $index) {
            if (isset($models[$index - 1])) {
                $this->generateController($models[$index - 1]);
            } else {
                $this->error("❌ Invalid selection: $index");
            }
        }
    }

    protected function generateAllControllers()
    {
        $models = $this->getAllModels();

        foreach ($models as $model) {
            $this->generateController($model);
        }
    }

    protected function generateController($modelName)
    {
        $controllerName = "{$modelName}Controller";
        $controllerPath = app_path("Http/Controllers/Api/{$controllerName}.php");

        if (File::exists($controllerPath) && !$this->confirm("The controller '$controllerName' already exists. Overwrite?")) {
            $this->info("⏩ Skipping $controllerName.");
            return;
        }

        $stubPath = app_path('Console/Commands/stubs/controller-api.stub');
        if (!File::exists($stubPath)) {
            $this->error("❌ Stub file missing at: $stubPath");
            return;
        }

        // Generate relations
        $relations = $this->getBelongsToRelations($modelName);
        $withQuery = $this->generateWithQuery($relations);
        $relationsList = $this->generateRelationsListWithoutNamespace($relations);
        $compactVariables = !empty($relations) 
        ? "'" . implode("', '", array_map([Str::class, 'plural'], $relations)) . "'" 
        : '';
        $editCompact = $compactVariables ? "'".lcfirst($modelName)."', $compactVariables" : "'".lcfirst($modelName)."'";
        $tableName = Str::plural(Str::snake($modelName));

        $relationsToload = $relations ? "$".lcfirst($modelName)."->load(['" . implode("', '", $relations) . "']);" : '';

        // Replace in stub
        $template = File::get($stubPath);
        $template = str_replace(
            [
                '{{model}}',
                '{{tableName}}',
                '{{modelVariable}}',
                '{{modelsVariable}}',
                '{{withQuery}}',
                '{{relationsList}}',
                '{{createReturnStatement}}',
                '{{editReturnStatement}}',
                '{{relationsToload}}'
            ],
            [
                $modelName,
                $tableName,
                lcfirst($modelName),
                lcfirst(Str::plural($modelName)),
                $withQuery,
                $relationsList,
                "return view('{$tableName}.create'" . ($compactVariables ? ", compact($compactVariables)" : "") . ");",
                "return view('{$tableName}.edit', compact($editCompact));",
                $relationsToload
            ],
            $template
        );

        File::put($controllerPath, $template);
        $this->info("✅ Controller '$controllerName' created successfully.");
    }

    protected function getAllModels()
    {
        $modelPath = app_path('Models');
        if (!is_dir($modelPath)) {
            return [];
        }

        $models = [];
        foreach (scandir($modelPath) as $file) {
            if ($file !== '.' && $file !== '..' && str_ends_with($file, '.php')) {
                $models[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }

        return $models;
    }

    protected function fileStatus($path)
    {
        return File::exists(app_path($path . '.php')) ? '<fg=red>✅</>' : '<fg=red>❌</>';
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

    protected function generateWithQuery($relations)
    {
        return !empty($relations) ? "with(['" . implode("', '", $relations) . "'])->" : "";
    }

    protected function generateRelationsListWithoutNamespace($relations)
    {
        if (empty($relations)) return '';

        $relationsList = "";
        foreach ($relations as $relation) {
            $relatedModel = Str::studly($relation);
            $varName = Str::plural($relation);
            $relationsList .= "\$$varName = $relatedModel::orderBy('name')->pluck('name', 'id')->toArray();\n        ";
        }
        return $relationsList;
    }
}
