<?php

namespace Kareem22t\StructureMyModule;

use Illuminate\Support\ServiceProvider;
use Kareem22t\StructureMyModule\Commands\CreateModule;
use Kareem22t\StructureMyModule\Commands\MakeAuthMvc;
use Illuminate\Support\Facades\Route;

class StructureMyModuleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateModule::class,
                MakeAuthMvc::class,
            ]);
        }
        $moduleRoutes = glob(base_path('routes/*.php'));
        foreach ($moduleRoutes as $routeFile) {
            Route::middleware('web')->group($routeFile);
        }
    }
}
