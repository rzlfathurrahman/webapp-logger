<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ScanProjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scan:projects {path? : The root path to scan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan directories for Laravel or Node projects';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = $this->argument('path');

        if (!$path) {
            $path = $this->ask('Please enter the root path to scan', base_path('..'));
        }

        if (!is_dir($path)) {
            $this->error("Directory not found: $path");
            return;
        }

        $this->info("Scanning directory: $path");

        // Scan for composer.json
        $this->scanForComposer($path);
        // Scan for package.json
        $this->scanForPackage($path);

        $this->info("Scan complete.");
    }

    protected function scanForComposer($path)
    {
        $finder = new \Symfony\Component\Finder\Finder();
        $finder->files()
            ->name('composer.json')
            ->in($path)
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
            ->exclude(['vendor', 'node_modules', 'storage', 'public'])
            ->depth('< 5'); // Limit depth to avoid deep recursions

        foreach ($finder as $file) {
            $filePath = $file->getRealPath();
            $projectPath = dirname($filePath);

            // Skip if already registered as laravel (from previous scan or dual detection)
            // But here we are scanning composer first.

            try {
                $content = json_decode(file_get_contents($filePath), true);
                if (!$content)
                    continue;

                $type = 'php';
                $logPath = null;
                $name = $content['name'] ?? basename($projectPath);

                if (isset($content['require']['laravel/framework'])) {
                    $type = 'laravel';
                    $logPath = $projectPath . '/storage/logs/laravel.log';
                }

                if ($type === 'laravel') { // For now focusing on Laravel
                    $this->info("Found Laravel Project: $name at $projectPath");
                    \App\Models\MonitoredProject::updateOrCreate(
                        ['path' => $projectPath],
                        [
                            'name' => $name,
                            'log_path' => $logPath,
                            'type' => $type,
                        ]
                    );
                }
            } catch (\Exception $e) {
                // ignore
            }
        }
    }

    protected function scanForPackage($path)
    {
        // Implementation for package.json if needed
        // For now, user emphasized Laravel log access.
        // We can add Node logic later.
    }
}
