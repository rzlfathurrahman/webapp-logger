# Logger Project Guide

## Setup
The project is already initialized. If you moved it or need to reinstall:
1. `composer install`
2. `npm install && npm run build`
3. `php artisan migrate`

## How to Run
You need to run multiple processes. You can use separate terminal tabs:

1. **Start WebSocket Server**:
   ```bash
   php artisan reverb:start
   ```

2. **Start Log Watcher**:
   ```bash
   php artisan watch:logs
   ```

3. **Start Web Server**:
   ```bash
   php artisan serve
   ```
   (Access at http://127.0.0.1:8000)

4. **(Optional) Start Vite Dev Server** if you want hot reloading:
   ```bash
   npm run dev
   ```

## How to Use
1. **Scan for Projects**:
   To add projects to the monitor, run:
   ```bash
   php artisan scan:projects /path/to/your/projects/folder
   ```
   It recursively searches for `composer.json` and adds Laravel projects automatically.

2. **View Logs**:
   Open `http://127.0.0.1:8000`. You will see the list of projects on the left.
   - Click a project to filter logs.
   - Initial logs will appear as they are written to the file (tailing).

## Features implemented
- **Project Scanner**: `app/Console/Commands/ScanProjects.php`
- **Log Watcher**: `app/Console/Commands/WatchLogs.php`
- **WebSocket Event**: `app/Events/LogEntryCreated.php`
- **Frontend**: `resources/views/dashboard.blade.php` using Laravel Echo & Tailwind CDN.
