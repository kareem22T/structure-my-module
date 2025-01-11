<?php

namespace Kareem22t\StructureMyModule\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class MakeStructureCommand extends Command
{
    protected $signature = 'make:structure-module {name : The name of the module}';
    protected $description = 'Create a new structured module with model, controller, and other components';

    public function handle()
    {
        $name = $this->argument('name');
        $moduleName = Str::studly($name);

        $baseDir = app_path("Modules/{$moduleName}");

        $directories = [
            "{$baseDir}/Controllers",
            "{$baseDir}/Models",
            "{$baseDir}/Views",
            "{$baseDir}/Routes",
            "{$baseDir}/Services",
            "{$baseDir}/Repositories",
            "{$baseDir}/Database/Migrations",
            "{$baseDir}/Database/Seeders",
        ];

        foreach ($directories as $directory) {
            File::makeDirectory($directory, 0755, true);
        }

        $this->createFiles($moduleName);

        $this->info("Module {$moduleName} created successfully!");
    }

    protected function createFiles($name)
    {
        $stubs = [
            'model' => "Models/{$name}.php",
            'controller' => "Controllers/{$name}Controller.php",
            'service' => "Services/{$name}Service.php",
            'repository' => "Repositories/{$name}Repository.php",
            'routes' => "Routes/web.php",
        ];

        foreach ($stubs as $type => $path) {
            $content = File::get(__DIR__ . "/../stubs/{$type}.stub");
            $content = str_replace(
                ['{{moduleName}}', '{{modelName}}', '{{namespace}}'],
                [$name, $name, 'Kareem22t\StructureMyModule'],
                $content
            );

            File::put(app_path("Modules/{$name}/{$path}"), $content);
        }
    }
}
