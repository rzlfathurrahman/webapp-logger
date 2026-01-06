<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = \App\Models\MonitoredProject::all();
        return view('dashboard', compact('projects'));
    }

    public function projects()
    {
        $projects = \App\Models\MonitoredProject::orderBy('name')->get();
        return view('projects.index', compact('projects'));
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'name' => 'nullable|string',
        ]);

        $path = rtrim($request->path, '/');

        if (!is_dir($path)) {
            return back()->withErrors(['path' => 'Directory not found.']);
        }

        // Auto-detect if not provided
        $name = $request->name ?? basename($path);
        $logPath = null;
        $type = 'custom';

        // Simple detection similar to scanner
        if (file_exists($path . '/composer.json')) {
            $content = json_decode(file_get_contents($path . '/composer.json'), true);
            if (isset($content['require']['laravel/framework'])) {
                $type = 'laravel';
                $logPath = $path . '/storage/logs/laravel.log';
                if (!$request->name)
                    $name = $content['name'] ?? $name;
            }
        }

        \App\Models\MonitoredProject::updateOrCreate(
            ['path' => $path],
            [
                'name' => $name,
                'log_path' => $logPath ?? ($path . '/storage/logs/laravel.log'), // Default guess
                'type' => $type,
            ]
        );

        return back()->with('success', 'Project added successfully.');
    }

    public function destroy($id)
    {
        \App\Models\MonitoredProject::findOrFail($id)->delete();
        return back()->with('success', 'Project removed.');
    }

    private function parseLine($line, $projectId, $projectName)
    {
        if (preg_match('/^\[(.*?)\]\s+(\w+)\.(\w+):\s+(.*)/', $line, $matches)) {
            $message = $matches[4];
            $context = null;

            // Attempt to find JSON context (Object or Array)
            $candidates = [];

            $p1 = strpos($message, '{');
            if ($p1 !== false)
                $candidates[] = $p1;

            $p2 = strpos($message, '[');
            if ($p2 !== false)
                $candidates[] = $p2;

            if (!empty($candidates)) {
                $jsonStart = min($candidates);
                $possibleJson = substr($message, $jsonStart);
                $decoded = json_decode($possibleJson, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $context = $decoded;
                    $message = trim(substr($message, 0, $jsonStart));
                }
            }

            return [
                'timestamp' => $matches[1],
                'env' => $matches[2],
                'level' => $matches[3],
                'message' => $message,
                'context' => $context,
                'project_id' => $projectId,
                'project_name' => $projectName,
                'raw' => $line
            ];
        }

        return [
            'timestamp' => '',
            'env' => 'RAW',
            'level' => 'LOG',
            'message' => $line,
            'project_id' => $projectId,
            'project_name' => $projectName,
            'raw' => $line
        ];
    }

    public function logs($id)
    {
        $project = \App\Models\MonitoredProject::findOrFail($id);

        if (!$project->log_path || !file_exists($project->log_path)) {
            return response()->json(['logs' => []]);
        }

        // Read last 100 lines efficiently using tail command
        $output = [];
        exec("tail -n 100 " . escapeshellarg($project->log_path), $output);

        $logs = [];
        $logs = [];
        foreach ($output as $line) {
            if (empty(trim($line)))
                continue;
            $logs[] = $this->parseLine($line, $project->id, $project->name);
        }

        return response()->json(['logs' => $logs]);
    }

    public function clear($id)
    {
        $project = \App\Models\MonitoredProject::findOrFail($id);

        if ($project->log_path && file_exists($project->log_path)) {
            file_put_contents($project->log_path, '');
        }

        return response()->json(['message' => 'Log file cleared.']);
    }

    public function recentLogs()
    {
        $projects = \App\Models\MonitoredProject::all();
        $allLogs = [];

        foreach ($projects as $project) {
            if (!$project->log_path || !file_exists($project->log_path))
                continue;

            // Get last 20 lines from each project
            $output = [];
            exec("tail -n 20 " . escapeshellarg($project->log_path), $output);

            foreach ($output as $line) {
                if (empty(trim($line)))
                    continue;
                $allLogs[] = $this->parseLine($line, $project->id, $project->name);
            }
        }

        // Sort by timestamp if possible, else keep order
        usort($allLogs, function ($a, $b) {
            return strcmp($a['timestamp'], $b['timestamp']);
        });

        // Limit to last 100 total
        $allLogs = array_slice($allLogs, -100);

        return response()->json(['logs' => $allLogs]);
    }
}
