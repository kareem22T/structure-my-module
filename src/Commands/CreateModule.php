<?php

namespace Kareem22t\StructureMyModule\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateModule extends Command
{
    protected $signature = 'make:module {name}';
    protected $description = 'Create a module with a controller, repository, service, resource, and other related files.';

    public function handle()
    {
        $name = $this->argument('name');
        $pluralName = Str::plural($name);

        // Generate Model, Request, Resource, and Collection
        $this->call('make:model', ['name' => $name]);
        $this->call('make:request', ['name' => "{$name}Request"]);
        $this->call('make:resource', ['name' => "{$name}Resource"]);
        $this->call('make:resource', ['name' => "{$name}Collection"]);

        // Define stubs and their target paths
        $stubPaths = [
            [
                'name' => 'Controller',
                'stub' => __DIR__ . '/../stubs/controller.stub',
                'target' => app_path("Http/Controllers/{$name}Controller.php"),
            ],
            [
                'name' => 'Repository Interface',
                'stub' => __DIR__ . '/../stubs/repository-interface.stub',
                'target' => app_path("Repositories/{$name}RepositoryInterface.php"),
            ],
            [
                'name' => 'Repository Implementation',
                'stub' => __DIR__ . '/../stubs/repository.stub',
                'target' => app_path("Repositories/{$name}Repository.php"),
            ],
            [
                'name' => 'Service',
                'stub' => __DIR__ . '/../stubs/service.stub',
                'target' => app_path("Services/{$name}Service.php"),
            ],
        ];

        // Create files from stubs
        foreach ($stubPaths as $stubPath) {
            $this->createFileFromStub($stubPath['stub'], $stubPath['target'], $name, $pluralName);
            $this->info("{$stubPath['name']} created at: {$stubPath['target']}");
        }

        // Update AppServiceProvider to bind the repository
        $this->updateServiceProvider($name);
        $this->info("Repository binding added to AppServiceProvider.");
    }

    /**
     * Create a file from a stub.
     */
    protected function createFileFromStub($stubPath, $targetPath, $name, $pluralName)
    {
        $stubContent = File::get($stubPath);
        $content = str_replace(['{{name}}', '{{names}}'], [$name, $pluralName], $stubContent);

        File::makeDirectory(dirname($targetPath), 0755, true, true);
        File::put($targetPath, $content);
    }

    /**
     * Update AppServiceProvider to bind the repository interface to its implementation.
     */
    protected function updateServiceProvider($name)
    {
        $interface = "App\\Repositories\\{$name}RepositoryInterface";
        $repository = "App\\Repositories\\{$name}Repository";

        $serviceProviderPath = app_path('Providers/AppServiceProvider.php');
        $bindStatement = "\$this->app->bind({$interface}::class, {$repository}::class);";

        if (File::exists($serviceProviderPath)) {
            $content = File::get($serviceProviderPath);

            // Add the binding only if it doesn't exist
            if (!Str::contains($content, $bindStatement)) {
                $content = preg_replace(
                    '/public function register\(\)\n\s*{\n/',
                    "public function register()\n    {\n        {$bindStatement}\n",
                    $content
                );
                File::put($serviceProviderPath, $content);
            }
        }
    }
}