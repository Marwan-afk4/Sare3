<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DaroryModels extends Command
{
    protected $signature = 'darory:models {table?} {--all}';
    protected $description = 'Generate a Laravel model based on a table name, including relationships.';
    protected array $skipTables = ['cache','cache_locks','job_batches','jobs','sessions','migrations', 'failed_jobs', 'password_resets','password_reset_tokens', 'personal_access_tokens'];

    public function handle()
    {
        
        if (app()->environment('production')) {
            $this->error("This command cannot be executed in production.");
            return;
        }
        
        $this->displayDaroryLogo();
        $tableName = $this->argument('table');

        if ($this->option('all')) {
            $this->generateAllModels();
            return;
        }

        if (!$tableName) {
            $tables = $this->getAllDatabaseTables();

            if (empty($tables)) {
                $this->error("❌ No tables found in the database.");
                return;
            }

            // Remove skipped tables
            $tables = array_values(array_diff($tables, $this->skipTables));

            $tableData = [];
            $missingTables = [];
            foreach ($tables as $index => $table) {
                $modelName = Str::studly(Str::singular($table));
                $modelExists = File::exists(app_path("Models/{$modelName}.php"));

                $tableData[] = [
                    '#'           => $index + 1,
                    'Table Name'  => $table,
                    'Model'       => $this->fileIcon($modelExists),
                    'Store Req'   => $this->fileIcon(File::exists(app_path("Http/Requests/{$modelName}StoreRequest.php"))),
                    'Update Req'  => $this->fileIcon(File::exists(app_path("Http/Requests/{$modelName}UpdateRequest.php"))),
                    'Controller'  => $this->fileIcon(File::exists(app_path("Http/Controllers/{$modelName}Controller.php"))),
                ];

                if (!$modelExists) {
                    $missingTables[] = $table;
                }
            }

            // Display table with "Select All" and "Generate Missing" options
            $this->table(['#', 'Table Name', 'Model', 'Store Req', 'Update Req', 'Controller'], $tableData);

            $input = $this->ask("\nEnter table numbers separated by commas, '0' for all, or 'm' for missing models:");

            if (strtoupper($input) === 'm') {
                if (empty($missingTables)) {
                    $this->info("✅ All models already exist.");
                    return;
                }
                foreach ($missingTables as $tableName) {
                    $this->generateModel($tableName);
                }
                return;
            }

            if ($input === '0') {
                $this->generateAllModels();
                return;
            }

            // Convert input to array of numbers
            $selectedIndexes = array_map('intval', explode(',', $input));

            $selectedTables = [];
            foreach ($selectedIndexes as $index) {
                if (isset($tableData[$index - 1])) {
                    $selectedTables[] = $tableData[$index - 1]['Table Name'];
                }
            }

            if (empty($selectedTables)) {
                $this->error("❌ No valid tables selected.");
                return;
            }

            foreach ($selectedTables as $tableName) {
                if (!Schema::hasTable($tableName)) {
                    $this->error("❌ The table '$tableName' does not exist in the database.");
                    continue;
                }
                $this->generateModel($tableName);
            }
        } else {
            if (!Schema::hasTable($tableName)) {
                $this->error("❌ The table '$tableName' does not exist in the database.");
                return;
            }
            $this->generateModel($tableName);
        }
    }

    protected function fileIcon($exists)
    {
        return $exists ? '✅' : '❌';
    }

    protected function displayDaroryLogo()
    {
        $logo = "Darory"; // Reset color
        $this->line($logo);
        $this->info("🚀 Welcome to the DARORY Model Generator!\n");
    }

    protected function generateAllModels()
    {
        foreach ($this->getAllDatabaseTables() as $table) {
            if (in_array($table, $this->skipTables)) {
                $this->info("⏩ Skipping table '$table'.");
                continue;
            }
            $this->generateModel($table);
        }
    }

    protected function generateModel($tableName)
    {
        $modelName = Str::studly(Str::singular($tableName));
        $modelPath = app_path("Models/{$modelName}.php");

        if (File::exists($modelPath) && !$this->confirm("The model '$modelName' already exists. Overwrite?")) {
            $this->info("⏩ Skipping $modelName model.");
            return;
        }

        $stubPath = app_path('Console/Commands/stubs/model.stub');
        if (!File::exists($stubPath)) {
            $this->error("❌ Stub file missing at: $stubPath");
            return;
        }

        $fillable = $this->getTableColumns($tableName);
        $relationships = $this->generateRelationships($tableName);

        $hasTimestamps = Schema::hasColumn($tableName, 'created_at') && Schema::hasColumn($tableName, 'updated_at');
        $timestampsCode = $hasTimestamps ? "public \$timestamps = true;" : "public \$timestamps = false;";

        $template = File::get($stubPath);
        $template = str_replace(
            ['{{model}}', '{{tableName}}', '{{fillable}}', '{{relationships}}', '{{timestamps}}'],
            [$modelName, $tableName, $fillable, $relationships, $timestampsCode],
            $template
        );

        File::put($modelPath, $template);
        $this->info("✅ Model '$modelName' created successfully.");
    }

    protected function getAllDatabaseTables()
    {
        return array_diff(array_map('current', DB::select('SHOW TABLES')), $this->skipTables);
    }

    protected function getTableColumns($tableName)
    {
        $columns = Schema::getColumnListing($tableName);
        $excludedColumns = ['id', 'created_at', 'updated_at'];
        $filteredColumns = array_diff($columns, $excludedColumns);
        return "[\n        '" . implode("',\n        '", $filteredColumns) . "'\n    ];";
    }

    protected function generateRelationships($tableName)
    {
        $relationships = '';

        // BelongsTo Relations (Existing Code)
        foreach (Schema::getColumnListing($tableName) as $column) {
            if (Str::endsWith($column, '_id')) {
                $relatedTable = Str::plural(Str::beforeLast($column, '_id'));
                $methodName = Str::camel(Str::beforeLast($column, '_id'));
                $relatedModel = Str::studly(Str::singular($relatedTable));

                if (!Schema::hasTable($relatedTable)) {
                    $this->warn("⚠️ Table '$relatedTable' does not exist. but generate BelongsTo relation.");
                    //continue;
                }

                $relationships .= "\n    public function {$methodName}()\n    {";
                $relationships .= "\n        return \$this->belongsTo({$relatedModel}::class);";
                $relationships .= "\n    }\n";
            }
        }

        // HasMany Relations
        foreach ($this->getAllDatabaseTables() as $otherTable) {
            if ($otherTable === $tableName) {
                continue;
            }

            foreach (Schema::getColumnListing($otherTable) as $column) {
                if ($column === Str::singular($tableName) . '_id') {
                    $methodName = Str::plural(Str::camel(Str::singular($otherTable)));
                    $relatedModel = Str::studly(Str::singular($otherTable));

                    $relationships .= "\n    public function {$methodName}()\n    {";
                    $relationships .= "\n        return \$this->hasMany({$relatedModel}::class);";
                    $relationships .= "\n    }\n";
                }
            }
        }

        return $relationships;
    }


    protected function generateCustomModel($modelName, $tableName)
    {
        $modelPath = app_path("Models/{$modelName}.php");

        if (File::exists($modelPath)) {
            $this->info("Model '{$modelName}' already exists. Skipping creation.");
            return;
        }

        $fillable = $this->getTableColumns($tableName);
        $relationships = $this->generateRelationships($tableName);

        $template = "<?php

    namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

    class {$modelName} extends Model
    {
        use HasFactory;

        protected \$table = '{$tableName}';

        protected \$fillable = {$fillable}

        {$relationships}
    }
    ";

        File::put($modelPath, $template);
        $this->info("✅ Model '{$modelName}' created successfully with table '{$tableName}'.");
    }
}
