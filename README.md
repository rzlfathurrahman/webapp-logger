# AG Logger

A centralized Laravel log monitor that allows you to watch logs from multiple local projects in real-time using WebSockets.

## Features
- **Real-time Monitoring**: Logs stream instantly to the dashboard via Laravel Reverb (WebSockets).
- **Multi-Project Scanning**: Recursively scan directories to find project `composer.json` files and automatically register them.
- **Manual Management**: Add or remove projects manually via the UI.
- **Log History**: View the last 100 lines of logs when selecting a project.
- **Log Truncation**: Clear log files on the server directly from the dashboard.
- **Log Parsing**: Automatic detection of Log Levels (INFO, ERROR, WARNING) with color coding.

## Requirements
- PHP 8.2+
- Composer
- Node.js & NPM
- SQLite (default database)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository_url>
   cd logger
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Install Node Dependencies & Build Assets**
   ```bash
   npm install
   npm run build
   ```

4. **Environment Setup**
   Copy `.env.example` to `.env` and configure:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   **Important**: Ensure the following configurations in `.env`:
   ```ini
   BROADCAST_CONNECTION=reverb
   QUEUE_CONNECTION=sync
   DB_CONNECTION=sqlite
   ```

5. **Setup Database**
   ```bash
   touch database/database.sqlite
   php artisan migrate
   ```

6. **Start Reverb (WebSocket Server)**
   ```bash
   php artisan reverb:start
   ```

## Running the Application

You need to run these 3 commands in separate terminal tabs/windows:

1. **WebSocket Server**
   ```bash
   php artisan reverb:start
   ```

2. **Log Watcher** (The background process that tails files)
   ```bash
   php artisan watch:logs
   ```

3. **Web Server**
   ```bash
   php artisan serve
   ```

Access the dashboard at: [http://127.0.0.1:8000](http://127.0.0.1:8000)

## Usage

### Scanning Projects
To automatically find and add Laravel projects:
```bash
php artisan scan:projects /path/to/your/projects/directory
```

### Dashboard
- **All Projects**: Shows a live stream of logs from all active projects (if implemented) or acts as a landing page.
- **Select Project**: Click a project in the sidebar to view its specific logs.
- **Clear Console**: Clears the visible logs in the browser.
- **Truncate File**: (Visible when hovering a project or selecting one) Deletes the actual log content on the disk to reset it.

## Troubleshooting
- **"Waiting for logs..." stuck**:
  - Ensure `php artisan watch:logs` is running.
  - Check browser console for WebSocket errors.
  - Verify `QUEUE_CONNECTION=sync` in `.env`.
- **Logs not appearing**:
  - Make sure the project's `storage/logs/laravel.log` is writable and actually being written to by the application.
