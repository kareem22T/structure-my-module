<?php

namespace Kareem22t\StructureMyModule\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateModule extends Command
{
    protected $signature = 'make:test-file {name}';
    protected $description = 'Create a test file from stub';

    public function handle()
    {
        $name = $this->argument('name');

        $this->call('make:model', ['name' => $name]);
        $this->call('make:request', ['name' => $name . 'Request']);


        $stubPaths  = [
            [
                'name' => 'Controller',
                'path' => __DIR__ . '/../stubs/controller.stub',
                'target' => app_path("Http/Controllers/{$name}.php")
            ]
        ];

        foreach ($stubPaths as $stubPath) {
            $stubContent = File::get($stubPath['path']);
            $content = str_replace('{{name}}', $name, $stubContent);
            $pluralName = Str::plural($name);
            $content = str_replace('{{names}}', $pluralName, $content);
            File::makeDirectory(dirname($stubPath['target']), 0755, true, true);
            File::put($stubPath['target'], $content);
            $this->info("{$stubPath['name']} file created: {$stubPath['target']}");
        }
    }
}
