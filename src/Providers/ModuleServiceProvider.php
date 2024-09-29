<?php

namespace Smony\Module\Providers;

use Route;
use Smony\Module\Commands\ListModulesCommand;
use Smony\Module\Commands\MakeModuleCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ModuleServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('module')
            ->hasConfigFile('module')
            ->hasCommands([
                MakeModuleCommand::class,
                ListModulesCommand::class,
            ]);
    }

    public function packageBooted()
    {
        $enabledModulesFile = base_path('enabled_modules.json');
        $moduleDirectory = config('module.module_directory', 'Modules');

        if (file_exists($enabledModulesFile)) {
            $modules = json_decode(file_get_contents($enabledModulesFile), true);

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
