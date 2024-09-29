<?php

namespace Smony\Module\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Str;

class MakeModuleCommand extends Command
{
    protected $signature = 'module:create {name} {--empty} {--d}';
    protected $description = 'Create a new module';
    protected Filesystem $filesystem;
    protected string $moduleDirectory;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
        $this->moduleDirectory = config('module.module_directory', 'Modules');
    }

    protected function disableModule($name): void
    {
        $this->updateModuleStatus($name, false);
        $this->info("$name module is disabled.");
    }

    protected function enableModule($name): void
    {
        $this->updateModuleStatus($name, true);
        $this->info("$name module enabled.");
    }

    protected function updateModuleStatus($name, $status): void
    {
        $statusesFile = base_path('enabled_modules.json');
        $modules = [];

        if ($this->filesystem->exists($statusesFile)) {
            $modules = json_decode($this->filesystem->get($statusesFile), true);
        }

        $modules[$name] = $status;

        $this->filesystem->put($statusesFile, json_encode($modules, JSON_PRETTY_PRINT));
    }

    protected function makeDirectory($path): void
    {
        if (!$this->filesystem->exists($path)) {
            $this->filesystem->makeDirectory($path, 0755, true);
        }
    }

    protected function createProvider($name): void
    {
        $providerTemplate = <<<EOT
        <?php

        namespace App\\{$this->moduleDirectory}\\$name\Providers;

        use Illuminate\Support\ServiceProvider;

        class {$name}ServiceProvider extends ServiceProvider
        {
            public function register()
            {
                //
            }

            public function boot()
            {
                \$this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
                \$this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');

                \$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

            }
        }
        EOT;

        $this->filesystem->put("app/$this->moduleDirectory/$name/Providers/{$name}ServiceProvider.php", $providerTemplate);
    }

    protected function createRoutesFile($name): void
    {
        $routeName = strtolower($name);

        $routeTemplate = <<<EOT
        <?php

        use Illuminate\Support\Facades\Route;

        Route::get('/$routeName', '{$name}Controller@index');
        EOT;

        $this->filesystem->put("app/$this->moduleDirectory/$name/Routes/web.php", $routeTemplate);
    }

    protected function createModuleStatusFile($name, $status = true): void
    {
        $statusesFile = base_path('enabled_modules.json');
        $modules = [];

        if ($this->filesystem->exists($statusesFile)) {
            $modules = json_decode($this->filesystem->get($statusesFile), true);
        }

        $modules[$name] = $status;

        $this->filesystem->put($statusesFile, json_encode($modules, JSON_PRETTY_PRINT));
    }

    protected function createApiRoutesFile($name): void
    {
        $routeName = strtolower($name);

        $apiRouteTemplate = <<<EOT
        <?php

        use Illuminate\Support\Facades\Route;

        Route::get('/$routeName', '{$name}ApiController@index');
        EOT;

        $this->filesystem->put("app/$this->moduleDirectory/$name/Routes/api.php", $apiRouteTemplate);
    }

    protected function createWebController($name): void
    {
        $controllerTemplate = <<<EOT
        <?php

        namespace App\\$this->moduleDirectory\\$name\Controllers;

        use Illuminate\Http\Request;
        use App\Http\Controllers\Controller;

        class {$name}Controller extends Controller
        {
            public function index()
            {
                return view('{$name}::index');
            }
        }
        EOT;

        $this->filesystem->put("app/$this->moduleDirectory/$name/Controllers/{$name}Controller.php", $controllerTemplate);
    }

    protected function createApiController($name): void
    {
        $apiControllerTemplate = <<<EOT
        <?php

        namespace App\\$this->moduleDirectory\\$name\Controllers\Api;

        use Illuminate\Http\Request;
        use App\Http\Controllers\Controller;

        class {$name}ApiController extends Controller
        {
            public function index()
            {
                return response()->json(['message' => 'API for $name']);
            }
        }
        EOT;

        $this->filesystem->put("app/$this->moduleDirectory/$name/Controllers/Api/{$name}ApiController.php", $apiControllerTemplate);
    }

    protected function createBaseMigration($module): void
    {
        $moduleLower = strtolower($module);
        $tableName = Str::plural(Str::snake($moduleLower));
        $tableFirst = ucfirst($tableName);
        $migrationName = date('Y_m_d_His') . "_create_{$tableName}_table.php";
        $migrationPath = base_path("app/Modules/$module/Database/Migrations/$migrationName");

        $migrationTemplate = <<<EOT
        <?php

        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\Schema;

        class Create{$tableFirst}Table extends Migration
        {
            public function up()
            {
                Schema::create('{$tableName}', function (Blueprint \$table) {
                    \$table->id();
                    \$table->timestamps();
                });
            }

            public function down()
            {
                Schema::dropIfExists('{$tableName}');
            }
        }
        EOT;

        $this->filesystem->put($migrationPath, $migrationTemplate);
        $this->info("Migration for $module created.");
    }

    protected function createModuleJson($name): void
    {
        $moduleJsonPath = "app/$this->moduleDirectory/$name/module.json";
        $moduleJsonTemplate = <<<EOT
        {
            "name": "$name",
            "version": "1.0.0",
            "description": "Description module $name",
            "enabled": true
        }
        EOT;

        $this->filesystem->put($moduleJsonPath, $moduleJsonTemplate);
        $this->info("File for $name created.");
    }

    protected function createModel($name): void
    {
        $modelName = rtrim($name, 's');

        $tableName = Str::plural(Str::snake($name));
        $tableFirst = ucfirst($modelName);

        $modelTemplate = <<<EOT
        <?php

        namespace App\\$this->moduleDirectory\\{$name}\Models;

        use Illuminate\Database\Eloquent\Model;

        class {$tableFirst} extends Model
        {
            protected \$table = '{$tableName}';

            protected \$fillable = [
                //
            ];
        }
        EOT;

        $this->filesystem->put(base_path("app/Modules/{$name}/Models/{$modelName}.php"), $modelTemplate);
        $this->info("Base model for $name created.");
    }

    public function handle(): void
    {
        $name = $this->argument('name');

        $modulePath = "app/" . $this->moduleDirectory . "/$name";

        if ($this->filesystem->exists($modulePath)) {
            $this->error("Module '$name' already exists!");
            return;
        }

        if ($this->option('d')) {
            $this->disableModule($name);
        } else {
            $this->enableModule($name);
        }

        $this->makeDirectory($modulePath);
        $this->makeDirectory("$modulePath/Controllers");
        $this->makeDirectory("$modulePath/Controllers/Api");
        $this->makeDirectory("$modulePath/Routes");
        $this->makeDirectory("$modulePath/Providers");

        if (!$this->option('empty')) {
            $this->makeDirectory("$modulePath/Models");
            $this->makeDirectory("$modulePath/Database/Migrations");
            $this->makeDirectory("$modulePath/Views");
        }

        $this->createProvider($name);

        $this->createWebController($name);
        $this->createApiController($name);

        $this->createRoutesFile($name);
        $this->createApiRoutesFile($name);

        if (!$this->option('empty')) {
            $this->createModel($name);
            $this->createBaseMigration($name);
        }

        $this->createModuleStatusFile($name);

        $this->createModuleJson($name);

        $this->info("Module $name created successfully.");
    }
}
