<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Logger</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom scrollbar for dark theme */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #1f2937;
        }

        ::-webkit-scrollbar-thumb {
            background: #4b5563;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }
    </style>
</head>

<body class="bg-gray-900 text-gray-200 font-mono h-screen flex overflow-hidden">
    <!-- Sidebar -->
    <div class="w-64 bg-gray-800 border-r border-gray-700 flex flex-col shadow-lg z-10">
        <div class="p-4 border-b border-gray-700 font-bold text-xl text-teal-400 flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                    </path>
                </svg>
                AG Logger
            </div>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="text-gray-400 hover:text-white" title="Add Project">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-2 space-y-2">
            @if(session('success'))
                <div class="p-2 mb-2 bg-green-900/50 text-green-300 text-xs rounded border border-green-800">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="p-2 mb-2 bg-red-900/50 text-red-300 text-xs rounded border border-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <div id="project-card-all"
                class="p-2 bg-gray-600 border border-teal-500 rounded cursor-pointer hover:bg-gray-600 transition project-card"
                onclick="filterLog(null, 'All Projects - Live Stream')">
                <div class="font-bold text-sm">All Projects</div>
            </div>
            @foreach($projects as $project)
                <div id="project-card-{{ $project->id }}"
                    class="p-2 bg-gray-750 border border-gray-700 rounded cursor-pointer hover:bg-gray-700 transition group relative project-card"
                    onclick="filterLog({{ $project->id }}, '{{ addslashes($project->name) }}')">
                    <div class="font-bold text-sm text-gray-300 group-hover:text-white pr-6">{{ $project->name }}</div>
                    <div class="text-xs text-gray-500 truncate group-hover:text-gray-400">{{ $project->path }}</div>
                    <div class="text-xs mt-1 flex justify-between items-center">
                        <span
                            class="px-1.5 py-0.5 rounded bg-blue-900 text-blue-300 text-[10px] uppercase font-bold tracking-wider">{{ $project->type }}</span>
                    </div>

                    <div class="absolute top-2 right-2 hidden group-hover:flex space-x-1">
                        <form action="{{ route('projects.clear', $project->id) }}" method="POST"
                            onsubmit="return confirm('Truncate log file for {{ $project->name }}?')">
                            @csrf
                            <button type="submit"
                                class="p-1 text-gray-500 hover:text-yellow-400 bg-gray-800 rounded transition"
                                title="Truncate Log File" onclick="event.stopPropagation()">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg>
                                <!-- Using eraser icon or similar -->
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    style="display:none">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                </svg>
                            </button>
                        </form>
                        <form action="{{ route('projects.destroy', $project->id) }}" method="POST"
                            onsubmit="return confirm('Remove project {{ $project->name }} from monitoring?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="p-1 text-gray-500 hover:text-red-400 bg-gray-800 rounded transition"
                                title="Remove Project" onclick="event.stopPropagation()">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="p-2 text-xs text-gray-600 text-center border-t border-gray-700">
            Scanning for new logs...
        </div>
    </div>

    <!-- Add Project Modal -->
    <div id="addModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center backdrop-blur-sm">
        <div class="bg-gray-800 rounded-lg shadow-xl border border-gray-700 w-full max-w-md p-6">
            <h2 class="text-lg font-bold text-white mb-4">Add Project Manually</h2>
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-400 text-sm font-bold mb-2">Project Path</label>
                    <input type="text" name="path" placeholder="/Users/macbook/Project/cool-app" required
                        class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-white focus:outline-none focus:border-teal-500">
                </div>
                <div class="mb-6">
                    <label class="block text-gray-400 text-sm font-bold mb-2">Name (Optional)</label>
                    <input type="text" name="name" placeholder="My Cool App"
                        class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-white focus:outline-none focus:border-teal-500">
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded text-gray-300">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 bg-teal-600 hover:bg-teal-500 rounded text-white font-bold">Add
                        Project</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col bg-gray-900 relative">
        <div class="p-4 border-b border-gray-700 bg-gray-800 flex justify-between items-center shadow-sm">
            <h1 class="text-lg font-semibold flex items-center">
                <span id="current-filter" class="mr-2">Live Logs</span>
                <span id="connection-status" class="w-2 h-2 rounded-full bg-yellow-500" title="Connecting..."></span>
            </h1>
            <div class="space-x-2 flex items-center">
                <button id="btn-clear-file" onclick="clearLogFile()"
                    class="hidden px-3 py-1.5 bg-red-900/50 hover:bg-red-800 rounded text-xs text-red-200 border border-red-800/50 transition">Truncate
                    File</button>
                <div class="h-4 w-px bg-gray-700 mx-2"></div>
                <button onclick="clearLogs()"
                    class="px-3 py-1.5 bg-gray-700 hover:bg-gray-600 rounded text-xs text-white border border-gray-600 transition">Clear
                    Console</button>
                <label class="inline-flex items-center text-xs cursor-pointer select-none">
                    <input type="checkbox" id="autoScroll"
                        class="form-checkbox bg-gray-700 border-gray-600 text-teal-500 rounded focus:ring-teal-500 focus:ring-offset-gray-800"
                        checked>
                    <span class="ml-2">Auto-scroll</span>
                </label>
            </div>
        </div>

        <div id="logs-container" class="flex-1 overflow-y-auto p-4 space-y-1 font-mono text-sm">
            <!-- Logs appear here -->
            <div class="text-gray-600 italic text-center text-xs mt-4">Waiting for logs...</div>
        </div>
    </div>

    <script>
        // Global state
        let activeProjectId = null;

        function filterLog(projectId, projectName) {
            activeProjectId = projectId;

            // Update Sidebar Active State
            document.querySelectorAll('.project-card').forEach(el => {
                // Reset to inactive
                el.classList.remove('bg-gray-600', 'border-teal-500');
                el.classList.add('bg-gray-750', 'border-gray-700');
            });

            // Set active
            const activeId = projectId ? `project-card-${projectId}` : 'project-card-all';
            const activeEl = document.getElementById(activeId);
            if (activeEl) {
                activeEl.classList.remove('bg-gray-750', 'border-gray-700');
                activeEl.classList.add('bg-gray-600', 'border-teal-500');
            }

            clearLogs();
            document.getElementById('current-filter').innerText = projectName || (projectId ? `Project #${projectId}` : 'All Projects - Live Stream');

            // Toggle Clear File button
            const btnClearFile = document.getElementById('btn-clear-file');
            if (projectId) {
                btnClearFile.classList.remove('hidden');

                // Fetch history
                document.getElementById('logs-container').innerHTML = '<div class="text-gray-500 text-xs text-center mt-4">Loading history...</div>';

                axios.get(`/projects/${projectId}/logs`)
                    .then(response => {
                        const logs = response.data.logs;
                        clearLogs(); // Remove "Loading..."
                        logs.forEach(log => appendLog(log));
                    })
                    .catch(err => {
                        console.error(err);
                        document.getElementById('logs-container').innerHTML = '<div class="text-red-500 text-xs text-center mt-4">Failed to load logs.</div>';
                    });
            } else {
                 btnClearFile.classList.add('hidden');

                 // Fetch recent logs for all projects
                 document.getElementById('logs-container').innerHTML = '<div class="text-gray-500 text-xs text-center mt-4">Loading recent logs...</div>';

                 axios.get('/projects/recent-logs')
                     .then(response => {
                         const logs = response.data.logs;
                         clearLogs();
                         if (logs.length === 0) {
                             document.getElementById('logs-container').innerHTML = '<div class="text-gray-500 text-xs text-center mt-4">No recent logs found. Waiting for live stream...</div>';
                         } else {
                             logs.forEach(log => appendLog(log));
                             // Add separator
                             const container = document.getElementById('logs-container');
                             const separator = document.createElement('div');
                             separator.className = "text-center text-xs text-gray-600 my-2 border-t border-gray-800 pt-2";
                             separator.innerText = "--- End of History / Start Live Stream ---";
                             container.appendChild(separator);
                         }
                     })
                     .catch(err => {
                         console.error(err);
                         document.getElementById('logs-container').innerHTML = '<div class="text-red-500 text-xs text-center mt-4">Failed to load recent logs.</div>';
                     });
        }
 }

        function clearLogs() {
            document.getElementById('logs-container').innerHTML = '';
        }

        function clearLogFile() {
            if (!activeProjectId) return;
            if (!confirm('Are you sure you want to TRUNCATE the log file? This cannot be undone.')) return;

            axios.post(`/projects/${activeProjectId}/clear`)
                .then(() => {
                    clearLogs();
                    alert('Log file cleared.');
                })
                .catch(err => alert('Failed to clear log file.'));
        }

        function appendLog(data) {
            // Check filter
            if (activeProjectId && activeProjectId != data.project_id) {
                return; // Do not append if not matching current filter
            }

            // If viewing "All" (activeProjectId null), we show everything

            const container = document.getElementById('logs-container');

            // Remove messages
            const messages = ['Waiting for logs...', 'Loading history...', 'Select a project '];
            if (container.firstChild && messages.some(msg => container.firstChild.innerText && container.firstChild.innerText.includes(msg))) {
                container.innerHTML = '';
            }

            const div = document.createElement('div');
            div.className = `log-entry hover:bg-gray-800 p-1 rounded group border-b border-gray-800/50 flex items-start space-x-2 animate-fade-in-up`;
            div.dataset.projectId = data.project_id;

            // Color based on level
            let levelColor = 'text-gray-400';
            let bgColor = '';
            if (data.level === 'ERROR' || data.level === 'CRITICAL' || data.level === 'EMERGENCY') {
                levelColor = 'text-red-400';
                bgColor = 'bg-red-900/10';
            }
            else if (data.level === 'WARNING' || data.level === 'ALERT') {
                levelColor = 'text-yellow-400';
                bgColor = 'bg-yellow-900/10';
            }
            else if (data.level === 'INFO' || data.level === 'NOTICE') {
                levelColor = 'text-blue-400';
            }
            else if (data.level === 'DEBUG') {
                levelColor = 'text-purple-400';
            }

            if (bgColor) div.classList.add(bgColor);

            let contextHtml = '';
            if (data.context) {
                const contextId = 'ctx-' + Math.random().toString(36).substr(2, 9);
                const prettyJson = JSON.stringify(data.context, null, 2);
                contextHtml = `
                    <div class="w-full mt-1 ml-40 pl-2">
                        <button onclick="document.getElementById('${contextId}').classList.toggle('hidden')" class="text-[10px] text-teal-600 hover:text-teal-400 underline focus:outline-none flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                            Show Context Data
                        </button>
                        <pre id="${contextId}" class="hidden mt-1 p-2 bg-gray-950 rounded text-green-300 text-[10px] overflow-x-auto border border-gray-800">${prettyJson}</pre>
                    </div>
                `;
            }

            div.innerHTML = `
                <div class="flex items-start space-x-2 w-full">
                    <span class="text-gray-500 text-xs whitespace-nowrap opacity-60 w-32 font-mono">${data.timestamp}</span>
                    <div class="relative group/name w-24">
                         <span class="text-teal-600 text-xs font-bold whitespace-nowrap truncate block w-full cursor-help">[${data.project_name}]</span>
                         <span class="absolute left-0 top-0 z-50 bg-gray-800 text-teal-400 border border-gray-600 px-2 py-1 rounded shadow-lg text-xs font-bold whitespace-nowrap hidden group-hover/name:block">
                            [${data.project_name}]
                         </span>
                    </div>
                    <span class="${levelColor} font-bold text-xs w-16 text-center whitespace-nowrap">${data.level}</span>
                    <span class="text-gray-300 break-words flex-1">${data.message}</span>
                </div>
                ${contextHtml}
            `;

            // Adjust div class to flex-col for proper layout of context below
            div.className = `log-entry hover:bg-gray-800 p-1 rounded group border-b border-gray-800/50 flex flex-col items-start space-y-1 animate-fade-in-up`;

            container.appendChild(div);

            // Limit log entries to prevent memory issues
            if (container.children.length > 500) {
                container.removeChild(container.firstChild);
            }

            const autoScroll = document.getElementById('autoScroll').checked;
            if (autoScroll) {
                container.scrollTop = container.scrollHeight;
            }
        }

        // Initialize Echo with retry
        // Initialize Echo with retry
        function initEcho() {
            if (window.Echo) {
                console.log('Echo loaded, connecting...');

                // Monitor connection status
                if (window.Echo.connector && window.Echo.connector.pusher) {
                    window.Echo.connector.pusher.connection.bind('connected', () => {
                        console.log('CONNECTED to WebSocket');
                        const statusEl = document.getElementById('connection-status');
                        statusEl.classList.remove('bg-yellow-500', 'bg-red-500');
                        statusEl.classList.add('bg-green-500', 'animate-pulse');
                        statusEl.title = "Connected";
                    });

                    window.Echo.connector.pusher.connection.bind('unavailable', () => {
                        console.log('WebSocket unavailable');
                         const statusEl = document.getElementById('connection-status');
                        statusEl.classList.remove('bg-green-500', 'bg-yellow-500');
                        statusEl.classList.add('bg-red-500');
                        statusEl.title = "Disconnected";
                    });

                    window.Echo.connector.pusher.connection.bind('failed', () => {
                        console.log('WebSocket failed');
                         const statusEl = document.getElementById('connection-status');
                        statusEl.classList.remove('bg-green-500', 'bg-yellow-500');
                        statusEl.classList.add('bg-red-500');
                        statusEl.title = "Connection Failed";
                    });
                }

                window.Echo.channel('logs')
                    .listen('.log.new', (e) => {
                        console.log('Received log (.log.new):', e);
                        appendLog(e.data);
                    })
                    .listen('log.new', (e) => {
                         console.log('Received log (log.new):', e);
                         appendLog(e.data);
                    })
                    .listen('.LogEntryCreated', (e) => {
                         console.log('Received log (.LogEntryCreated):', e);
                         appendLog(e.data);
                    })
                    .listen('LogEntryCreated', (e) => {
                         console.log('Received log (LogEntryCreated):', e);
                         appendLog(e.data);
                    });

            } else {
                console.log('Echo not ready yet, retrying...');
                setTimeout(initEcho, 100);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            initEcho();
        });
    </script>
</body>

</html>