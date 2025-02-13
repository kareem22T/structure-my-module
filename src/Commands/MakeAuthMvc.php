<?php

namespace Kareem22t\StructureMyModule\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeAuthMvc extends Command
{
    protected $signature = 'make:auth-mvc {name} {--guard=} {--views}';
    protected $description = 'Create an auth MVC module with a controller, repository, service, and related files.';


    public function handle()
    {
        $name = $this->argument('name');
        $lowercase_name = Str::lower($this->argument('name'));
        $guard = $this->option('guard') ?? 'web'; // Default to 'web' if not provided
        $generateViews = $this->option('views'); // Check if --view flag is passed
        $plural_name = Str::plural($lowercase_name);

        // Validate name (uppercase and singular)
        if ($name !== Str::ucfirst(Str::singular($name))) {
            $this->error("The 'name' must be uppercase and singular.");
            return Command::INVALID;
        }

        // Define stubs for auth-mvc
        $stubPaths = [
            [
                'name' => 'Add Model',
                'stub' => __DIR__ . '/../stubs/auth-mvc/model.stub',
                'target' => app_path("/Models/{$name}.php"),
            ],
            [
                'name' => 'Add Migration',
                'stub' => __DIR__ . '/../stubs/auth-mvc/migration.stub',
                'target' => database_path("migrations/" . now()->format('Y_m_d_His') . "_create_{$plural_name}_table.php"),
            ],
            [
                'name' => 'Routes File',
                'stub' => __DIR__ . '/../stubs/auth-mvc/routes.stub',
                'target' => base_path("routes/{$lowercase_name}.php"),
            ],
            [
                'name' => 'Repository Interface',
                'stub' => __DIR__ . '/../stubs/auth-mvc/interface.stub',
                'target' => app_path("Repositories/Web/{$name}/Auth/AuthRepositoryInterface.php"),
            ],
            [
                'name' => 'Repository Implementation',
                'stub' => __DIR__ . '/../stubs/auth-mvc/repo.stub',
                'target' => app_path("Repositories/Web/{$name}/Auth/AuthRepository.php"),
            ],
            [
                'name' => 'Service',
                'stub' => __DIR__ . '/../stubs/auth-mvc/service.stub',
                'target' => app_path("Services/Web/{$name}/Auth/AuthService.php"),
            ],
            [
                'name' => 'Controller',
                'stub' => __DIR__ . '/../stubs/auth-mvc/controller.stub',
                'target' => app_path("Http/Controllers/Web/{$name}/Auth/AuthController.php"),
            ],
            [
                'name' => 'Register Request',
                'stub' => __DIR__ . '/../stubs/auth-mvc/register_request.stub',
                'target' => app_path("Http/Requests/Web/{$name}/Auth/RegisterRequest.php"),
            ],
            [
                'name' => 'Login Request',
                'stub' => __DIR__ . '/../stubs/auth-mvc/login_request.stub',
                'target' => app_path("Http/Requests/Web/{$name}/Auth/LoginRequest.php"),
            ],
            [
                'name' => 'Update Profile Request',
                'stub' => __DIR__ . '/../stubs/auth-mvc/update_profile_request.stub',
                'target' => app_path("Http/Requests/Web/{$name}/Auth/UpdateProfileRequest.php"),
            ],
        ];

        if ($generateViews) {
            $viewStubs = [
                [
                    'name' => 'Error Partial Component',
                    'stub' => __DIR__ . '/../stubs/auth-mvc/_errors_blade.stub',
                    'target' => resource_path("views/{$lowercase_name}/auth/_errors.blade.php"),
                ],
                [
                    'name' => 'Message Partial Component',
                    'stub' => __DIR__ . '/../stubs/auth-mvc/_message_blade.stub',
                    'target' => resource_path("views/{$lowercase_name}/auth/_messages.blade.php"),
                ],
                [
                    'name' => 'Login Blade',
                    'stub' => __DIR__ . '/../stubs/auth-mvc/login_blade.stub',
                    'target' => resource_path("views/{$lowercase_name}/auth/login.blade.php"),
                ],
                [
                    'name' => 'Register Blade',
                    'stub' => __DIR__ . '/../stubs/auth-mvc/register_blade.stub',
                    'target' => resource_path("views/{$lowercase_name}/auth/register.blade.php"),
                ],
                [
                    'name' => 'Update Profile Blade',
                    'stub' => __DIR__ . '/../stubs/auth-mvc/edit_profile_blade.stub',
                    'target' => resource_path("views/{$lowercase_name}/auth/edit-profile.blade.php"),
                ],
                [
                    'name' => 'Show Profile Blade',
                    'stub' => __DIR__ . '/../stubs/auth-mvc/profile_blade.stub',
                    'target' => resource_path("views/{$lowercase_name}/auth/profile.blade.php"),
                ],
            ];
            $stubPaths = array_merge($stubPaths, $viewStubs);
        }


        // Create files from stubs
        foreach ($stubPaths as $stubPath) {
            $target = $stubPath['target'];
            $this->createFileFromStub($stubPath['stub'], $target, $name, $lowercase_name, $guard, $plural_name);
            $this->info("INFO: [{$stubPath['name']}] created successfully at: {$target}");
        }
    }

    protected function createFileFromStub($stubPath, $targetPath, $name, $lowercase_name, $guard, $plural_name)
    {
        $stubContent = File::get($stubPath);
        $content = str_replace(
            ['{{name}}', '{{lowercase_name}}', '{{guard_name}}', '{{plural_name}}'],
            [$name, $lowercase_name, $guard, $plural_name],
            $stubContent
        );

        File::makeDirectory(dirname($targetPath), 0755, true, true);
        File::put($targetPath, $content);
    }
}
