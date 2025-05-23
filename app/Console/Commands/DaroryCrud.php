<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;

class DaroryCrud extends Command
{

    protected $signature = 'darory:crud {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate models, requests, controllers, and views based on the given name';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        if (app()->environment('production')) {
            $this->error("This command cannot be executed in production.");
            return;
        }
        
        $name = $this->argument('name');

        $modelName = Str::studly(Str::singular($name));

        $this->call('darory:models', ['table' => $name]);
        $this->call('darory:requests', ['model' => $modelName]);
        $this->call('darory:controllers', ['model' => $modelName]);
        $this->call('darory:views', ['model' => $modelName]);

        $this->info('All resources generated successfully!');
    }
}
