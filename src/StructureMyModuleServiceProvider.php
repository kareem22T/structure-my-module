<?php

namespace Kareem22t\StructureMyModule;

use Illuminate\Support\ServiceProvider;
use Kareem22t\StructureMyModule\Commands\MakeStructureCommand;

class StructureMyModuleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeStructureCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/stubs' => base_path('stubs/vendor/structure-my-module'),
            ], 'structure-my-module-stubs');
        }
    }

    public function register()
    {
        //
    }
}
