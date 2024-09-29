<?php

namespace Smony\Module\Providers;

use Illuminate\Support\ServiceProvider;
use Route;
use Smony\Module\Commands\ListModulesCommand;
use Smony\Module\Commands\MakeModuleCommand;

class ModuleServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/module.php', 'module'
        );

        $moduleDirectory = config('module.module_directory', 'Modules');

        $enabledModulesFile = base_path('enabled_modules.json');
        if (file_exists($enabledModulesFile)) {
            $modules = json_decode(file_get_contents($enabledModulesFile), true);

            foreach ($modules as $module => $enabled) {
                if ($enabled) {
                    $provider = "App\\{$moduleDirectory}\\$module\\Providers\\{$module}ServiceProvider";

                    if (class_exists($provider)) {
                        $this->app->register($provider);
                    }
                }
            }
        }

        $this->commands([
            MakeModuleCommand::class,
            ListModulesCommand::class
        ]);
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../Config/module.php' => config_path('module.php'),
        ], 'config');

        $enabledModulesFile = base_path('enabled_modules.json');
        if (file_exists($enabledModulesFile)) {
            $modules = json_decode(file_get_contents($enabledModulesFile), true);
            $moduleDirectory = config('module.module_directory', 'Modules');

            foreach ($modules as $module => $enabled) {
                if ($enabled) {
                    $this->registerModuleRoutes($module, $moduleDirectory, 'App\\' . $moduleDirectory);
                }
            }
        }
    }

    protected function registerModuleRoutes($module, $moduleDirectory, $moduleNamespace): void
    {
        $webRoutes = base_path("App/$moduleDirectory/$module/Routes/web.php");
        $apiRoutes = base_path("App/$moduleDirectory/$module/Routes/api.php");

        if (file_exists($webRoutes)) {
            Route::namespace("$moduleNamespace\\$module\\Controllers")
            ->group(function () use ($webRoutes) {
                require $webRoutes;
            });
        }

        if (file_exists($apiRoutes)) {
            Route::prefix('api')
                ->namespace("$moduleNamespace\\$module\\Controllers\\Api")
                ->middleware('api')
                ->group(function () use ($apiRoutes) {
                    require $apiRoutes;
                });
        }
    }

}
