<?php

namespace Kareem22t\StructureMyModule;

use Illuminate\Support\ServiceProvider;
use Kareem22t\StructureMyModule\Commands\CreateModule;

class StructureMyModuleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateModule::class
            ]);
        }
    }
}
