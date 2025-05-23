<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DaroryViews extends Command
{
    protected $signature = 'darory:views {model?} {--views=*} {--all}';
    protected $description = 'Generate CRUD views from model schema';

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
                $this->error("Please provide a model name or use --all to generate views for all models.");
                return;
            }
            $this->generateViewsForModel($modelName);
        }
    }

    protected function generateForAllModels()
    {
        $modelFiles = File::files(app_path('Models'));
        foreach ($modelFiles as $file) {
            $modelName = pathinfo($file, PATHINFO_FILENAME);
            $this->generateViewsForModel($modelName);
        }
    }

    protected function generateViewsForModel($modelName)
    {
        $modelClass = "App\\Models\\$modelName";

        if (!class_exists($modelClass)) {
            $this->error("Model $modelName does not exist.");
            return;
        }

        $tableName = (new $modelClass)->getTable();
        $kebabName = Str::kebab(Str::headline($tableName));
        $columns = DB::getSchemaBuilder()->getColumnListing($tableName);

        $viewDir = resource_path("views/$kebabName");
        if (file_exists($viewDir)) {
            $overwriteDir = $this->ask("The directory '$kebabName' already exists. Do you want to overwrite existing views? (yes/no)");
            if (!in_array(strtolower($overwriteDir), ['yes', 'y'])) {
                $this->info("Operation canceled for $modelName.");
                return;
            }
        }

        $viewsToGenerate = empty($this->option('views')) ? ['index', 'create', 'edit', 'show'] : $this->option('views');
        foreach ($viewsToGenerate as $view) {
            $this->generateView($view, $modelName, $columns);
        }

        $this->info("CRUD views for $modelName generated successfully.");
    }

    protected function generateView($view, $modelName, $columns)
    {
        $tableName = Str::plural(Str::snake($modelName));
        $kebabName = Str::kebab(Str::headline($tableName));
        $viewPath = resource_path("views/$kebabName/$view.blade.php");
        File::ensureDirectoryExists(dirname($viewPath));

        $content = $this->getViewContent($view, $modelName, $columns);
        File::put($viewPath, $content);

        $this->info("Created view: $viewPath");
    }

    protected function getViewContent($view, $modelName, $columns)
    {
        $modelsValiable =  lcfirst(Str::plural($modelName));
        $modelVariable = lcfirst($modelName);
        $snakeModel = Str::snake($modelName);
        $tableName = Str::plural(Str::snake($modelName));
        $kebabName = Str::kebab(Str::headline($tableName));

        $hasMany = $this->getHasManyRelations($modelName);
        $hasManyLinks = $this->generateNavForHasMany($hasMany,$tableName,$modelVariable);

        switch ($view) {
            case 'index':
                return "@extends('layouts.app')"."\n".
                "@php"."\n".
                "\t\$currentPage = '$kebabName';\n".
                "@endphp\n".
                "@section('title', __('".Str::title(str_replace('_', ' ', $tableName))."'))\n".
                "@section('content')"."\n".
                "<div class=\"container-fluid\">"."\n".
                "\t"."<h1 class=\"mb-3\">{{__('".Str::title(str_replace('_', ' ', $tableName))."')}}</h1>"."\n".
                "\t<div class=\"mb-3\">\n".
                "\t\t<a href=\"{{ route('".$kebabName.".create') }}\" class=\"btn btn-primary btn-sm me-1\">{{__('Create ".Str::title(str_replace('_',' ', $snakeModel))."')}} <i class=\"fa fa-plus\"></i></a>\n".
                "\t</div>"."\n".
                "\t<div class='main-card mb-3 card'>"."\n".
                "\t\t<div class='card-body'>"."\n".
                "\t\t\t<table class=\"mb-0 table table-hover\">\n\t\t\t\t<tr>" .
                    implode('', array_map(fn($col) =>
                        (in_array($col, ['updated_at']) || preg_match('/token|password/i', $col)) ? '' :
                        "\n\t\t\t\t\t<th>"."\n".
                        "\t\t\t\t\t\t<a href=\"{{ route('".$kebabName.".index', ['sort' => '".$col."', 'order' => \$sortOrder === 'asc' ? 'desc' : 'asc']) }}\">\n".
                        "\t\t\t\t\t\t\t"."{{ __(\"".Str::title(str_replace(['_id', '_'], ['', ' '], $col))."\") }}"."\n".
                        "\t\t\t\t\t\t\t@if(\$sortField === '".$col."')".
                        "<i class=\"text-danger\">{{ \$sortOrder === 'asc' ? '▼' : '▲' }}</i>".
                        "@endif\n".
                        "\t\t\t\t\t\t</a>\n".
                        "\t\t\t\t\t</th>", $columns)) . "\n".
                    "\t\t\t\t\t"."<th class=\"text-center\">{{ __('Actions') }}</th>\n\t\t\t\t</tr>\n\t\t\t\t@foreach(\$$modelsValiable as \$$modelVariable)\n\t\t\t\t<tr>" .

                    implode('', array_map(fn($col) =>
                        (in_array($col, ['updated_at']) || preg_match('/token|password/i', $col)) ? '' :
                        "\n\t\t\t\t\t<td>{{ \$$modelVariable->".(Str::endsWith($col, '_id') ? str_replace(' ','',lcfirst(ucwords(str_replace('_', ' ', Str::replaceLast('_id', '', $col))))).'?->name' : $col)." }}</td>", $columns)) ."\n".

                    "\t\t\t\t\t<td class=\"text-center\">"."\n".
                    "\t\t\t\t\t\t<a href='{{ route('$kebabName.show', \$$modelVariable) }}' class=\"btn btn-subtle-primary btn-sm me-1\">{{ __(\"Details\") }} <i class=\"fa fa-eye\"></i></a>"."\n".
                    "\t\t\t\t\t\t<a href='{{ route('$kebabName.edit', \$$modelVariable) }}' class=\"btn btn-subtle-warning btn-sm me-1\">{{ __(\"Edit\") }} <i class=\"fa fa-edit\"></i></a>"."\n".
                    "\t\t\t\t\t\t{{-- <form method='POST' action='{{ route('$kebabName.destroy', \$$modelVariable) }}' onsubmit='return confirm(\"Are you sure you want to delete this item?\")'>\n".
                    "\t\t\t\t\t\t\t<input type='hidden' name='_method' value='DELETE'>\n".
                    "\t\t\t\t\t\t\t<button type='submit' class=\"btn btn-square btn-danger\">{{ __('Delete') }}</button>\n".
                    "\t\t\t\t\t\t</form> --}}\n".
                    "\t\t\t\t\t</td>\n\t\t\t\t</tr>"."\n".
                    "\t\t\t\t@endforeach"."\n".
                    "\t\t\t</table>\n".
                    "\t\t\t{{ \$$modelsValiable->"."links('pagination::custom') }}".
                    "\n\t\t</div>\n\t</div>\n</div>\n@endsection";

            case 'create':
                return "@extends('layouts.app')\n".
                "@php"."\n".
                "\t\$currentPage = '$kebabName';\n".
                "@endphp\n".
                "@section('title', __('Create ".Str::title(str_replace('_', ' ', $snakeModel))."'))\n".
                "@section('content')"."\n".
                "<div class=\"container\">\n".
                "\t<h1>{{ __('Create ".Str::title(str_replace('_', ' ', $snakeModel))."') }}</h1>\n".
                "\t<div class=\"mb-3\">\n".
                    "\t\t<a href=\"{{ route('".$kebabName.".index') }}\" class=\"btn btn-secondary btn-sm me-1\"> <i class=\"fa fa-arrow-right\"></i> {{__('Back to')}} {{__('".Str::plural(Str::title(str_replace('_', ' ', $tableName)))."')}}</a>\n".
                "\t</div>\n".
                "\t<div class=\"main-card mb-3 card\">\n".
                "\t\t<div class=\"card-body\">\n".
                "\t\t\t<form method='POST' action='{{ route('$kebabName.store') }}' class=\"needs-validation\" novalidate>\n".
                "\t\t\t\t@csrf\n" .
                    implode('', array_map(function($col) use ($snakeModel, $tableName) {
                        // Skip generating inputs for specific columns
                        if (in_array($col, ['id', 'created_at', 'updated_at', 'deleted_at']) || preg_match('/token|password/i', $col)) {
                            return '';
                        }

                        try {
                            $columnDetails = DB::getSchemaBuilder()->getConnection()->select(
                                "SHOW COLUMNS FROM `$tableName` WHERE Field = ?", [$col]
                            );
                        } catch (\Exception $e) {
                            return '';
                        }

                        $isRequired = !empty($columnDetails) && strpos($columnDetails[0]->Null, 'NO') !== false;

                        $inputType = $this->getInputType($columnDetails[0]->Type, $col);

                        if ($inputType === 'select') {
                            return "\t\t\t\t".
                            "<x-form-select \n\t\t\t\t\tname=\"".$col."\"\n\t\t\t\t\ttype=\"".$inputType."\"\n\t\t\t\t\tlabel=\"{{__('".Str::title(str_replace(['_id', '_'], ['', ' '], $col))."')}}\"\n\t\t\t\t\t:selected=\"\$".$snakeModel."->".$col." ?? ''\"" .
                            ($isRequired ? "\n\t\t\t\t\trequired" : "") ."\n\t\t\t\t\t:options=\"\$".Str::plural(Str::camel(Str::replaceLast('_id', '', $col)))."\"". "\n\t\t\t\t/>\n";
                        }


                        // Generate the input with the required attribute if applicable
                        return "\t\t\t\t".
                            "<x-form-input \n\t\t\t\t\tname=\"".$col."\"\n\t\t\t\t\ttype=\"".$inputType."\"\n\t\t\t\t\tlabel=\"{{__('".Str::title(str_replace(['_id', '_'], ['', ' '], $col))."')}}\"" .
                            ($isRequired ? "\n\t\t\t\t\trequired" : "") . "\n\t\t\t\t/>\n";
                    }, $columns)) .
                    "\t\t\t\t<button type='submit' class=\"btn btn-primary btn-sm me-1\">{{ __('Add') }}</button>\n".
                "\t\t\t</form>\n".
                "\t\t</div>\n".
                "\t</div>\n".
                "</div>".
                "@endsection";

                case 'edit':
                    return "@extends('layouts.app')\n".
                    "@php"."\n".
                    "\t\$currentPage = '$kebabName';\n".
                    "@endphp\n".
                    "@section('title', __('Edit ".Str::title(str_replace('_', ' ', $snakeModel))."'))\n".
                    "@section('content')"."\n".
                    "<div class=\"container\">\n".
                    "\t<h1>{{ __('Edit ".Str::title(str_replace('_', ' ', $snakeModel))."') }}</h1>\n".
                    "\t<div class=\"mb-3\">\n".
                        "\t\t<a href=\"{{ route('".$kebabName.".index') }}\" class=\"btn btn-secondary btn-sm me-1\"> <i class=\"fa fa-arrow-right\"></i> {{__('Back to')}} {{__('".Str::plural(Str::title(str_replace('_', ' ', $tableName)))."')}}</a>\n".
                        "\t\t<a href='{{ route('$kebabName.show', \$$modelVariable) }}' class=\"btn btn-primary btn-sm me-1\">{{ __(\"Details\") }} <i class=\"fa fa-eye\"></i></a>"."\n".
                        $hasManyLinks.
                    "\t</div>\n".
                    "\t<div class=\"main-card mb-3 card\">\n".
                    "\t\t<div class=\"card-body\">\n".
                    "\t\t\t<form method='POST' action='{{ route('$kebabName.update', $".$modelVariable."->id) }}' class=\"needs-validation\" novalidate>\n".
                    "\t\t\t\t@csrf\n" .
                    "\t\t\t\t@method('PUT')\n" .
                    implode('', array_map(function($col) use ($snakeModel, $tableName,$modelVariable) {
                        // Skip generating inputs for specific columns
                        if (in_array($col, ['id', 'created_at', 'updated_at', 'deleted_at']) || preg_match('/token|password/i', $col)) {
                            return '';
                        }

                        try {
                            $columnDetails = DB::getSchemaBuilder()->getConnection()->select(
                                "SHOW COLUMNS FROM `$tableName` WHERE Field = ?", [$col]
                            );
                        } catch (\Exception $e) {
                            return '';
                        }

                        $isRequired = !empty($columnDetails) && strpos($columnDetails[0]->Null, 'NO') !== false;

                        $inputType = $this->getInputType($columnDetails[0]->Type, $col);




                        // Generate the input with the required attribute if applicable
                        if ($inputType === 'select') {
                            return "\t\t\t\t".
                            "<x-form-select \n\t\t\t\t\tname=\"".$col."\"\n\t\t\t\t\ttype=\"".$inputType."\"\n\t\t\t\t\tlabel=\"{{__('".Str::title(str_replace(['_id', '_'], ['', ' '], $col))."')}}\"\n\t\t\t\t\t:selected=\"\$".$modelVariable."->".$col." ?? ''\"" .
                            ($isRequired ? "\n\t\t\t\t\trequired" : "") ."\n\t\t\t\t\t:options=\"\$".Str::plural(Str::camel(Str::replaceLast('_id', '', $col)))."\"". "\n\t\t\t\t/>\n";
                        }


                        // Generate the input with the required attribute if applicable
                        return "\t\t\t\t".
                            "<x-form-input \n\t\t\t\t\tname=\"".$col."\"\n\t\t\t\t\ttype=\"".$inputType."\"\n\t\t\t\t\tlabel=\"{{__('".Str::title(str_replace(['_id', '_'], ['', ' '], $col))."')}}\"" .
                            "\n\t\t\t\t\t:value=\"\$".$modelVariable."->".$col." ?? ''\"" .
                            ($isRequired ? "\n\t\t\t\t\trequired" : "") . "\n\t\t\t\t/>\n";
                    }, $columns)) .
                    "\t\t\t\t<button type='submit' class=\"btn btn-warning btn-sm me-1\">{{ __('Save') }}</button>\n".
                    "\t\t\t</form>\n".
                    "\t\t</div>\n".
                    "\t</div>\n".
                    "</div>\n".
                    "@endsection";

            case 'show':
                return "@extends('layouts.app')"."\n".
                "@php"."\n".
                "\t\$currentPage = '".$kebabName."';"."\n".
                "@endphp"."\n".
                "@section('title', $".$modelVariable."->name)"."\n".
                "@section('content')\n".
                "<div class=\"container-fluid\">"."\n".
                "\t<h1>{{ \$".$modelVariable."->name }}</h1>\n".
                "\t<div class=\"mb-3\">\n".
                    "\t\t<a href=\"{{ route('".$kebabName.".index') }}\" class=\"btn btn-secondary btn-sm me-1\"> <i class=\"fa fa-arrow-right\"></i> {{__('Back to')}} {{__('".Str::plural(Str::title(str_replace('_', ' ', $tableName)))."')}}</a>\n".
                    "\t\t<a href='{{ route('$kebabName.edit', \$$modelVariable) }}' class=\"btn btn-warning btn-sm me-1\">{{ __('Edit') }} <i class=\"fa fa-edit\"></i></a>\n".
                    $hasManyLinks.
                "\t</div>\n".
                "\t<div class=\"card\">\n".
                    "\t\t<div class=\"card-body\">\n".
                "\t\t\t<ul class=\"list-group list-group-flush\">\n" .
                    implode('', array_map(function($col) use ($snakeModel,$modelVariable) {
                        // Skip sensitive columns
                        if (preg_match('/token|password/i', $col)) {
                            return '';
                        }

                        return "\t\t\t\t<li class=\"list-group-item\">\n" .
                            "\t\t\t\t\t<strong>{{ __(\"".Str::title(str_replace(['_id', '_'], ['', ' '], $col))."\") }}:</strong> " .
                            "{{ \$$modelVariable->" . (Str::endsWith($col, '_id') ? str_replace(' ','',lcfirst(ucwords(str_replace('_', ' ', Str::replaceLast('_id', '', $col))))) . '?->name' : $col) . " }}\n" .
                            "\t\t\t\t</li>\n";
                    }, $columns)).
                    "\t\t\t</ul>\n".
                    "\t\t</div>\n".
                    "\t</div>\n".
                    "\t<div class=\"mt-3\">\n".
                    "\t\t{{-- <form method='POST' action='{{ route('$kebabName.destroy', \$$modelVariable) }}' onsubmit='return confirm(\"Are you sure you want to delete this item?\")'>\n".
                    "\t\t\t<input type='hidden' name='_method' value='DELETE'>\n".
                    "\t\t\t<button type='submit' class=\"btn btn-square btn-danger\">{{ __('Delete') }}</button>\n".
                    "\t\t</form> --}}\n".
                    "\t</div>\n".
                    "</div>\n".
                    "@endsection";
        }
    }

    protected function getBelongsToRelations($modelName)
    {
        if (!class_exists("App\\Models\\$modelName")) {
            return [];
        }

        $modelInstance = new ("App\\Models\\$modelName");
        $methods = get_class_methods($modelInstance);
        $relations = [];

        foreach ($methods as $method) {
            try {
                $reflection = new \ReflectionMethod($modelInstance, $method);
                if ($reflection->getNumberOfParameters() === 0) {
                    $returnType = $reflection->invoke($modelInstance);
                    if ($returnType instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
                        $relations[] = $method;
                    }
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return $relations;
    }

    protected function getHasManyRelations($modelName)
    {
        if (!class_exists("App\\Models\\$modelName")) {
            return [];
        }

        $modelInstance = new ("App\\Models\\$modelName");
        $methods = get_class_methods($modelInstance);
        $relations = [];

        foreach ($methods as $method) {
            try {
                $reflection = new \ReflectionMethod($modelInstance, $method);
                if ($reflection->getNumberOfParameters() === 0) {
                    $returnType = $reflection->invoke($modelInstance);
                    if ($returnType instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
                        $relations[] = $method;
                    }
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return $relations;
    }

    protected function generateNavForHasMany($relations,$tableName,$modelVariable){
        $kebabName = Str::kebab(Str::headline($tableName));
        if (empty($relations)):
            return '';
        endif;

        $nav = '';
        foreach ($relations as $relation) {
            $relatedModel = Str::singular(Str::afterLast(Str::beforeLast($relation, 'By'), 'HasMany'));
            $relatedTableName = Str::plural(Str::snake($relatedModel));
            $nav.= "\t\t{{-- <a href=\"{{ route('$kebabName.{$relatedTableName}', \$$modelVariable) }}\" class=\"list-group-item\">{{ __('".lcfirst(Str::title(str_replace('_','', $relatedModel)))."') }}</a> --}}\n";
        }

        return $nav;
    }

    protected function getInputType($columnType, $columnName)
    {
        if (Str::endsWith($columnName, '_id')) {
            return 'select';
        }

        switch ($columnType) {
            case 'string':
            case 'varchar':
                return 'text';
            case 'text':
                return 'text';
            case 'integer':
            case 'bigint':
                return 'number';
            case 'boolean':
                return 'checkbox';
            case 'date':
            case 'datetime':
                return 'date';
            case 'time':
                return 'time';
            case 'decimal':
            case 'float':
                return 'number';
            default:
                return 'text';
        }
    }
}
