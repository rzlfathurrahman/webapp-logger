<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Projects - Laravel Logger</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes progress {
            0% {
                width: 0%;
                margin-left: 0;
            }

            50% {
                width: 70%;
                margin-left: 30%;
            }

            100% {
                width: 0%;
                margin-left: 100%;
            }
        }

        .animate-progress {
            animation: progress 1.5s infinite ease-in-out;
        }
    </style>
</head>

<body class="bg-gray-900 text-gray-200 font-mono h-screen flex overflow-hidden">
    <!-- Progress Bar -->
    <div id="loading-bar" class="h-1 w-full bg-gray-800 hidden fixed top-0 left-0 z-50">
        <div class="h-full bg-teal-500 animate-progress"></div>
    </div>

    <!-- Sidebar (Simplified) -->
    <div class="w-64 bg-gray-800 border-r border-gray-700 flex flex-col shadow-lg z-10">
        <div class="p-4 border-b border-gray-700 font-bold text-xl text-teal-400 flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                </path>
            </svg>
            AG Logger
        </div>
        <div class="flex-1 p-2 space-y-2">
            <a href="{{ route('home') }}" onclick="showLoading()"
                class="block p-2 bg-gray-750 text-gray-400 hover:bg-gray-700 hover:text-white rounded transition flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                    </path>
                </svg>
                Live Logs
            </a>
            <a href="{{ route('projects.index') }}"
                class="block p-2 bg-gray-700 text-teal-400 font-bold rounded flex items-center border border-teal-500/30">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                    </path>
                </svg>
                Manage Projects
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col bg-gray-900 p-8 overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-white">Project Management</h1>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="px-4 py-2 bg-teal-600 hover:bg-teal-500 text-white rounded font-bold shadow-lg transition flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add New Project
            </button>
        </div>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-900/50 text-green-300 border border-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-900/50 text-red-300 border border-red-800 rounded">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="bg-gray-800 rounded-lg shadow-xl overflow-hidden border border-gray-700">
            <div class="p-4 border-b border-gray-700 flex items-center">
                <input type="text" id="searchInput" placeholder="Search projects..."
                    class="bg-gray-900 border border-gray-600 text-gray-300 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-64 p-2.5">
            </div>
            <table class="w-full text-sm text-left text-gray-400">
                <thead class="text-xs text-gray-400 uppercase bg-gray-750 border-b border-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3">ID</th>
                        <th scope="col" class="px-6 py-3">Name</th>
                        <th scope="col" class="px-6 py-3">Path</th>
                        <th scope="col" class="px-6 py-3">Type</th>
                        <th scope="col" class="px-6 py-3">Log Status</th>
                        <th scope="col" class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="projectTableBody">
                    @foreach($projects as $project)
                        <tr class="bg-gray-800 border-b border-gray-700 hover:bg-gray-750 transition">
                            <td class="px-6 py-4">{{ $project->id }}</td>
                            <td class="px-6 py-4 font-bold text-teal-400">{{ $project->name }}</td>
                            <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $project->path }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 rounded bg-blue-900/50 text-blue-300 text-xs font-bold border border-blue-800">{{ $project->type }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if(file_exists($project->log_path))
                                    <span class="flex items-center text-green-400 text-xs font-bold">
                                        <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span> Found
                                    </span>
                                @else
                                    <span class="flex items-center text-red-400 text-xs font-bold">
                                        <span class="w-2 h-2 rounded-full bg-red-500 mr-2"></span> Missing
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <form action="{{ route('projects.clear', $project->id) }}" method="POST"
                                    class="inline-block"
                                    onsubmit="return confirm('Truncate log file for {{ $project->name }}?')">
                                    @csrf
                                    <button type="submit" class="font-medium text-yellow-500 hover:underline">Truncate
                                        Logs</button>
                                </form>
                                <span class="text-gray-600">|</span>
                                <form action="{{ route('projects.destroy', $project->id) }}" method="POST"
                                    class="inline-block" onsubmit="return confirm('Delete project {{ $project->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-red-500 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    @if($projects->isEmpty())
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 italic">No projects monitored yet.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal (Reused) -->
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

    <script>
        function showLoading() {
            document.getElementById('loading-bar').classList.remove('hidden');
        }

        // Simple client-side search
        document.getElementById('searchInput').addEventListener('keyup', function (e) {
            const term = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#projectTableBody tr');

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                if (text.includes(term)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>

</html>