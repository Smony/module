<?php

namespace Smony\Module\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ListModulesCommand extends Command
{
    protected $signature = 'module:list';
    protected $description = 'Get list of modules';

    protected Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }

    public function handle()
    {
        $statusesFile = base_path('enabled_modules.json');

        if (!$this->filesystem->exists($statusesFile)) {
            $this->error('Modules not found');
            return;
        }

        $modules = json_decode($this->filesystem->get($statusesFile), true);

        if (empty($modules)) {
            $this->info('No modules available');
            return;
        }

        $headers = ['Name', 'Status', 'Version'];
        $data = [];

        $directoryModule = config('module.module_directory', 'Modules');
        $moduleVersion = 'N/A';

        foreach ($modules as $module => $enabled) {
            $moduleJsonPath = base_path("app/$directoryModule/$module/module.json");

            if ($this->filesystem->exists($moduleJsonPath)) {
                $moduleJson = json_decode($this->filesystem->get($moduleJsonPath), true);
                $moduleVersion = $moduleJson['version'] ?? 'N/A';
            }

            $data[] = [
                'module' => $module,
                'status' => $enabled ? 'On' : 'Off',
                'version' => $moduleVersion,
            ];
        }

        $this->table($headers, $data);
    }
}
