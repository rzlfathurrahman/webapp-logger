<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WatchLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'watch:logs';

    protected $description = 'Watch monitored logs and broadcast events';

    public function handle()
    {
        $this->info("Starting log watcher...");

        // Map: projectId => lastFileSize
        $fileStates = [];

        while (true) {
            $projects = \App\Models\MonitoredProject::all();

            foreach ($projects as $project) {
                if (!$project->log_path || !file_exists($project->log_path)) {
                    continue;
                }

                $path = $project->log_path;
                $currentSize = filesize($path);

                // Initialize state
                if (!isset($fileStates[$project->id])) {
                    $fileStates[$project->id] = $currentSize;
                    continue;
                }

                if ($currentSize > $fileStates[$project->id]) {
                    // File grew
                    $this->processNewLogs($project, $fileStates[$project->id], $currentSize);
                    $fileStates[$project->id] = $currentSize;
                } elseif ($currentSize < $fileStates[$project->id]) {
                    // File rotated/truncated
                    $fileStates[$project->id] = 0; // Reset or currentSize
                }
            }

            clearstatcache();
            sleep(1);
        }
    }

    protected function processNewLogs($project, $startPos, $endPos)
    {
        $content = file_get_contents($project->log_path, false, null, $startPos, $endPos - $startPos);

        if ($content === false)
            return;

        $lines = explode("\n", $content);
        $count = 0;

        foreach ($lines as $line) {
            if (empty(trim($line)))
                continue;

            $parsed = $this->parseLogLine($line);

            // Fallback for non-standard lines
            if (!$parsed) {
                $parsed = [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'env' => 'RAW',
                    'level' => 'LOG',
                    'message' => $line,
                    'raw' => $line
                ];
            }

            $payload = array_merge($parsed, [
                'project_id' => $project->id,
                'project_name' => $project->name,
            ]);

            // Identify level for color output in terminal
            $level = $parsed['level'];
            $this->output->writeln("<info>[{$project->name}]</info> Broadcasting: <comment>[$level]</comment> " . substr($parsed['message'], 0, 50));

            \App\Events\LogEntryCreated::dispatch($payload);
            $count++;
        }
    }

    protected function parseLogLine($line)
    {
        // Simple regex for Laravel default pattern
        // [2024-01-01 10:00:00] local.INFO: Message content
        // Regex: /^\[(.*?)\]\s+(\w+)\.(\w+):\s+(.*)/

        if (preg_match('/^\[(.*?)\]\s+(\w+)\.(\w+):\s+(.*)/', $line, $matches)) {
            return [
                'timestamp' => $matches[1],
                'env' => $matches[2],
                'level' => $matches[3],
                'message' => $matches[4],
                'raw' => $line
            ];
        }

        // If not matching, treat as continuation or raw
        // For simplicity, skip stack traces or handle differently?
        // Let's just return a generic raw object if it looks like a wrapped line,
        // OR only return valid starts.
        // Returning null skips parsing = skips broadcasting non-header lines (stack traces).
        // For a simple logger, maybe that's fine, or we want to append to previous?
        // Appending to previous is complex in stateless loop line-by-line.
        // Let's iterate lines and keep context if needed?

        return null;
    }
}
