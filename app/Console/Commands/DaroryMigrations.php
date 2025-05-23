<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DaroryMigrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'darory:migrate {table?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate migrations for existing tables';

    protected array $skipTables = [
        'cache',
        'cache_locks',
        'failed_jobs',
        'job_batches',
        'jobs',
        'migrations',
        'password_reset_tokens',
        'personal_access_tokens',
        'sessions'
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tables = DB::select('SHOW TABLES');
        $database = env('DB_DATABASE');

        foreach ($tables as $table) {
            $tableName = $table->{"Tables_in_$database"};
            
            if (in_array($tableName, $this->skipTables)) {
                continue; // Skip specified tables
            }

            $migrationName = 'create_' . $tableName . '_table';

            if ($this->migrationExists($migrationName)) {
                if (!$this->confirm("Migration for table '{$tableName}' already exists. Generate a new one?")) {
                    continue;
                }
            }

            $this->generateMigration($tableName);
        }

        $this->info('All migrations generated successfully!');
    }


    /**
     * Generate the migration file for a given table.
     *
     * @param string $tableName
     * @return void
     */
    protected function generateMigration($tableName)
    {
        $migrationName = 'create_' . $tableName . '_table';
        $migrationFileName = date('Y_m_d_His') . "_{$migrationName}.php";
        $migrationPath = database_path("migrations/{$migrationFileName}");

        $columns = Schema::getColumnListing($tableName);
        $stub = $this->generateMigrationStub($tableName, $columns);

        File::put($migrationPath, $stub);
        $this->info("Migration created for table: $tableName");
    }

    

    /**
     * Generate the migration stub.
     *
     * @param string $tableName
     * @param array $columns
     * @return string
     */
    protected function generateMigrationStub($tableName, $columns)
{
    $stub = "<?php\n\n";
    $stub .= "use Illuminate\Database\Migrations\Migration;\n";
    $stub .= "use Illuminate\Database\Schema\Blueprint;\n";
    $stub .= "use Illuminate\Support\Facades\Schema;\n\n";
    $stub .= "class Create" . Str::studly($tableName) . "Table extends Migration\n";
    $stub .= "{\n";
    $stub .= "    public function up()\n";
    $stub .= "    {\n";
    $stub .= "        Schema::create('$tableName', function (Blueprint \$table) {\n";

    $hasTimestamps = in_array('created_at', $columns) && in_array('updated_at', $columns);
    $morphColumns = [];
    $primaryKeys = $this->getPrimaryKeys($tableName);

    foreach ($columns as $column) {
        if ($hasTimestamps && ($column === 'created_at' || $column === 'updated_at')) {
            continue;
        }

        if (Str::endsWith($column, '_type')) {
            $prefix = Str::before($column, '_type');
            $idColumn = $prefix . '_id';
            if (in_array($idColumn, $columns)) {
                $morphColumns[$prefix] = true;
                continue;
            }
        }

        if (Str::endsWith($column, '_id') && isset($morphColumns[Str::before($column, '_id')])) {
            continue;
        }

        $columnType = Schema::getColumnType($tableName, $column);

        $typeMapping = [
            'bigint' => 'bigInteger',
            'int' => 'integer',
            'smallint' => 'smallInteger',
            'tinyint' => 'tinyInteger',
            'varchar' => 'string',
            'text' => 'text',
            'datetime' => 'dateTime',
            'timestamp' => 'timestamp',
            'boolean' => 'boolean',
            'float' => 'float',
            'double' => 'double',
            'decimal' => 'decimal',
            'json' => 'json'
        ];
        
        $columnType = $typeMapping[$columnType] ?? $columnType;
        
        if (in_array($column, $primaryKeys)) {
            if (count($primaryKeys) == 1 && $columnType === 'bigInteger') {
                $stub .= "            \$table->id(); // Primary key\n";
            } else {
                $stub .= "            \$table->$columnType('$column')->primary();\n";
            }
        } else {
            $stub .= "            \$table->string('$column');\n";
        }
    }

    foreach ($morphColumns as $prefix => $_) {
        $idColumn = $prefix . '_id';
        $isNullable = $this->isColumnNullable($tableName, $idColumn);
        $stub .= "            \$table->" . ($isNullable ? 'nullableMorphs' : 'morphs') . "('$prefix');\n";
    }

    if ($hasTimestamps) {
        $stub .= "            \$table->timestamps();\n";
    }

    $stub .= "        });\n";
    $stub .= "    }\n\n";
    $stub .= "    public function down()\n";
    $stub .= "    {\n";
    $stub .= "        Schema::dropIfExists('$tableName');\n";
    $stub .= "    }\n";
    $stub .= "}\n";

    return $stub;
}


    /**
     * Check if a column is nullable.
     *
     * @param string $tableName
     * @param string $columnName
     * @return bool
     */
    protected function isColumnNullable($tableName, $columnName)
    {
        try {
            $database = env('DB_DATABASE');

            $query = "
                SELECT IS_NULLABLE 
                FROM information_schema.COLUMNS 
                WHERE TABLE_NAME = ? 
                AND COLUMN_NAME = ? 
                AND TABLE_SCHEMA = ?
            ";

            $result = DB::select($query, [$tableName, $columnName, $database]);

            if (!empty($result)) {
                return $result[0]->IS_NULLABLE === 'YES';
            }
        } catch (\Exception $e) {
            $this->error("Error getting column details: " . $e->getMessage());
        }

        return false;
    }


    protected function getPrimaryKeys($tableName)
    {
        $primaryKeys = [];

        try {
            $database = env('DB_DATABASE');
            $query = "
                SELECT COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = ? 
                AND TABLE_SCHEMA = ? 
                AND CONSTRAINT_NAME = 'PRIMARY'
            ";
            
            $keys = DB::select($query, [$tableName, $database]);

            foreach ($keys as $key) {
                $primaryKeys[] = $key->COLUMN_NAME;
            }
        } catch (\Exception $e) {
            $this->error("Error getting primary key for table '$tableName': " . $e->getMessage());
        }

        return $primaryKeys;
    }

    protected function migrationExists($migrationName)
    {
        $migrationsPath = database_path('migrations');
        $files = File::files($migrationsPath);
    
        foreach ($files as $file) {
            if (Str::contains($file->getFilename(), $migrationName)) {
                return true;
            }
        }
    
        return false;
    }
    
}
