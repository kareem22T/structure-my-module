<?php

namespace Kareem22t\StructureMyModule\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateModule extends Command
{
    protected $signature = 'make:module {name} {type} {prefix?}';
    protected $description = 'Create a module with a controller, repository, service, resource, and other related files.';

    public function handle()
    {
        $name = $this->argument('name');
        $type = $this->argument('type');
        $prefix = $this->argument('prefix') ?? null;

        // Validate type (now including combined types)
        $validTypes = ['mvc', 'api', 'auth-mvc', 'auth-sanctum', 'both', 'auth-both'];
        if (!in_array($type, $validTypes)) {
            $this->error("The 'type' must be one of: " . implode(', ', $validTypes));
            return Command::INVALID;
        }

        // Validate name (uppercase and singular)
        if ($name !== Str::ucfirst(Str::singular($name))) {
            $this->error("The 'name' must be uppercase and singular.");
            return Command::INVALID;
        }

        // Validate prefix (uppercase and singular)
        if ($prefix && $prefix !== Str::ucfirst(Str::singular($prefix))) {
            $this->error("The 'prefix' must be uppercase and singular.");
            return Command::INVALID;
        }

        $pluralName = Str::plural($name);
        $prefixWithAppendSlashAfter = $prefix ? $prefix . '/' : '';

        // Generate Model, Resource for all types
        $this->call('make:model', ['name' => $name]);

        // Generate collection for API types
        if (in_array($type, ['api', 'auth-sanctum', 'both', 'auth-both'])) {
            $this->call('make:resource', ['name' => "{$prefixWithAppendSlashAfter}{$name}Resource"]);
            $this->call('make:resource', ['name' => "{$prefixWithAppendSlashAfter}{$name}Collection"]);
        }

        // Define stubs based on type
        $stubPaths = $this->getStubPaths($type, $name, $prefix);

        // Create files from stubs
        foreach ($stubPaths as $stubPath) {
            $target = $prefix ? str_replace('{{prefix}}', $prefix . '/', $stubPath['target']) : str_replace('{{prefix}}', '', $stubPath['target']);
            $this->createFileFromStub($stubPath['stub'], $target, $name, $pluralName, $prefix);
            $this->info("{$stubPath['name']} created at: {$target}");
        }
    }

    protected function getStubPaths($type, $name, $prefix)
    {
        $basePaths = [
            [
                'name' => 'Repository Interface',
                'stub' => __DIR__ . '/../stubs/repository-interface.stub',
                'target' => app_path("Repositories/{$name}RepositoryInterface.php"),
            ],
            [
                'name' => 'Repository Implementation',
                'stub' => __DIR__ . '/../stubs/repository.stub',
                'target' => app_path("Repositories/{{prefix}}{$name}Repository.php"),
            ],
            [
                'name' => 'Service',
                'stub' => __DIR__ . '/../stubs/service.stub',
                'target' => app_path("Services/{{prefix}}{$name}Service.php"),
            ],
        ];

        // Define paths based on type
        switch ($type) {
            case 'both':
                return array_merge($basePaths, [
                    [
                        'name' => 'Web Controller',
                        'stub' => __DIR__ . '/../stubs/controller-mvc.stub',
                        'target' => app_path("Http/Controllers/Web/{{prefix}}{$name}Controller.php"),
                    ],
                    [
                        'name' => 'API Controller',
                        'stub' => __DIR__ . '/../stubs/controller-api.stub',
                        'target' => app_path("Http/Controllers/API/{{prefix}}{$name}Controller.php"),
                    ],
                    [
                        'name' => 'Store Request',
                        'stub' => __DIR__ . '/../stubs/store-request.stub',
                        'target' => app_path("Http/Requests/{{prefix}}Store{$name}Request.php"),
                    ],
                    [
                        'name' => 'Update Request',
                        'stub' => __DIR__ . '/../stubs/update-request.stub',
                        'target' => app_path("Http/Requests/{{prefix}}Update{$name}Request.php"),
                    ]
                ]);

            case 'auth-both':
                return array_merge($basePaths, [
                    [
                        'name' => 'Web Auth Controller',
                        'stub' => __DIR__ . '/../stubs/auth-controller-mvc.stub',
                        'target' => app_path("Http/Controllers/Web/{{prefix}}{$name}Controller.php"),
                    ],
                    [
                        'name' => 'API Auth Controller',
                        'stub' => __DIR__ . '/../stubs/auth-controller-sanctum.stub',
                        'target' => app_path("Http/Controllers/API/{{prefix}}{$name}Controller.php"),
                    ],
                    [
                        'name' => 'Login Request',
                        'stub' => __DIR__ . '/../stubs/login-request.stub',
                        'target' => app_path("Http/Requests/{{prefix}}Login{$name}Request.php"),
                    ],
                    [
                        'name' => 'Register Request',
                        'stub' => __DIR__ . '/../stubs/register-request.stub',
                        'target' => app_path("Http/Requests/{{prefix}}Register{$name}Request.php"),
                    ],
                    [
                        'name' => 'Update Request',
                        'stub' => __DIR__ . '/../stubs/update-request.stub',
                        'target' => app_path("Http/Requests/{{prefix}}Update{$name}Request.php"),
                    ]
                ]);
            case 'mvc':
                return array_merge($basePaths, [
                    [
                        'name' => 'Controller',
                        'stub' => __DIR__ . '/../stubs/controller-mvc.stub',
                        'target' => app_path("Http/Controllers/Web/{{prefix}}{$name}Controller.php"),
                    ],
                    [
                        'name' => 'Store Request',
                        'stub' => __DIR__ . '/../stubs/store-request.stub',
                        'target' => app_path("Http/Requests/{{prefix}}Store{$name}Request.php"),
                    ],
                    [
                        'name' => 'Update Request',
                        'stub' => __DIR__ . '/../stubs/update-request.stub',
                        'target' => app_path("Http/Requests/{{prefix}}Update{$name}Request.php"),
                    ]
                ]);

            case 'api':
                return array_merge($basePaths, [
                    [
                        'name' => 'Controller',
                        'stub' => __DIR__ . '/../stubs/controller-api.stub',
                        'target' => app_path("Http/Controllers/API/{{prefix}}{$name}Controller.php"),
                    ],
                    [
                        'name' => 'Store Request',
                        'stub' => __DIR__ . '/../stubs/store-request.stub',
                        'target' => app_path("Http/Requests/{{prefix}}Store{$name}Request.php"),
                    ],
                    [
                        'name' => 'Update Request',
                        'stub' => __DIR__ . '/../stubs/update-request.stub',
                        'target' => app_path("Http/Requests/{{prefix}}Update{$name}Request.php"),
                    ]
                ]);

            case 'auth-mvc':
                return array_merge($basePaths, [
                    [
                        'name' => 'Controller',
                        'stub' => __DIR__ . '/../stubs/auth-controller-mvc.stub',
                        'target' => app_path("Http/Controllers/Web/{{prefix}}{$name}Controller.php"),
                    ],
                    [
                        'name' => 'Login Request',
                        'stub' => __DIR__ . '/../stubs/login-request.stub',
                        'target' => app_path("Http/Requests/{{prefix}}Login{$name}Request.php"),
                    ],
                    [
                        'name' => 'Register Request',
                        'stub' => __DIR__ . '/../stubs/register-request.stub',
                        'target' => app_path("Http/Requests/{{prefix}}Register{$name}Request.php"),
                    ],
                    [
                        'name' => 'Update Request',
                        'stub' => __DIR__ . '/../stubs/update-request.stub',
                        'target' => app_path("Http/Requests/{{prefix}}Update{$name}Request.php"),
                    ]
                ]);

            case 'auth-sanctum':
                return array_merge($basePaths, [
                    [
                        'name' => 'Controller',
                        'stub' => __DIR__ . '/../stubs/auth-controller-sanctum.stub',
                        'target' => app_path("Http/Controllers/API/{{prefix}}{$name}Controller.php"),
                    ],
                    [
                        'name' => 'Login Request',
                        'stub' => __DIR__ . '/../stubs/login-request.stub',
                        'target' => app_path("Http/Requests/{{prefix}}Login{$name}Request.php"),
                    ],
                    [
                        'name' => 'Register Request',
                        'stub' => __DIR__ . '/../stubs/register-request.stub',
                        'target' => app_path("Http/Requests/{{prefix}}Register{$name}Request.php"),
                    ],
                    [
                        'name' => 'Update Request',
                        'stub' => __DIR__ . '/../stubs/update-request.stub',
                        'target' => app_path("Http/Requests/{{prefix}}Update{$name}Request.php"),
                    ]
                ]);
        }
    }

    protected function createFileFromStub($stubPath, $targetPath, $name, $pluralName, $prefix = null)
    {
        $stubContent = File::get($stubPath);
        $content = str_replace(
            ['{{name}}', '{{names}}', '{{prefix}}'],
            [$name, $pluralName, $prefix ? '\\' . $prefix : ''],
            $stubContent
        );

        File::makeDirectory(dirname($targetPath), 0755, true, true);
        File::put($targetPath, $content);
    }
}