<?php

namespace Kareem22t\StructureMyModule\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TestCommand extends Command
{
    protected $signature = 'make:test-file {name}';
    protected $description = 'Create a test file from stub';

    public function handle()
    {
        $name = $this->argument('name');

        // Get stub content
        $stubPath = __DIR__ . '/../stubs/test.stub';
        $stubContent = File::get($stubPath);

        // Replace placeholder
        $content = str_replace('{{name}}', $name, $stubContent);

        // Create file
        $targetPath = app_path("Test/{$name}.php");

        // Create directory if it doesn't exist
        File::makeDirectory(dirname($targetPath), 0755, true, true);

        // Save file
        File::put($targetPath, $content);

        $this->info("Test file created: {$targetPath}");
    }
}