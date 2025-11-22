<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Support\Facades\File;
use Pterodactyl\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Pterodactyl\Http\Requests\Admin\AdminFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Pterodactyl\Models\User;

class LaravelController extends Controller
{
    public function __construct(
        private ViewFactory $view,
        private ConfigRepository $config,
        private SettingsRepositoryInterface $settings,
    ){}

    public function showLogs(Request $request): View
{
    if (!$request->user() || !$request->user()->root_admin) {
        throw new AccessDeniedHttpException();
    }

    $logDirPath = storage_path('logs');
    $logFiles = File::glob($logDirPath . '/laravel-*.log');

    $selectedLogFile = $request->input('log_file', end($logFiles));

    // Security check: Ensure the selected file is within the logs directory
    if ($selectedLogFile && !$this->isValidLogFile($selectedLogFile, $logDirPath)) {
        return $this->view->make('admin.laravel-logs.laravel', [
            'logs' => 'Access denied: File outside logs directory.',
            'logFiles' => $logFiles,
            'selectedLogFile' => $selectedLogFile,
        ]);
    }

    // Check if the selected log file exists    
    if (!File::exists($selectedLogFile)) {
        return $this->view->make('admin.laravel-logs.laravel', [
            'logs' => 'Log file does not exist.',
            'logFiles' => $logFiles,
            'selectedLogFile' => $selectedLogFile,
        ]);
    }

    $logs = collect(explode("\n", File::get($selectedLogFile)))->slice(-1000)->implode("\n");

    return $this->view->make('admin.laravel-logs.laravel', [
        'logs' => $logs,
        'logFiles' => $logFiles,
        'selectedLogFile' => $selectedLogFile,
    ]);
}

public function downloadLogs(Request $request)
{
    if (!$request->user() || !$request->user()->root_admin) {
        throw new AccessDeniedHttpException();
    }

    $logFile = $request->input('log_file');
    $logDirPath = storage_path('logs');

    // Security check: Ensure the requested file is within the logs directory
    if (!$this->isValidLogFile($logFile, $logDirPath)) {
        return redirect()->back()->with('error', 'Access denied: File outside logs directory.');
    }

    if (File::exists($logFile)) {
        return response()->download($logFile, basename($logFile))->deleteFileAfterSend(false);
    }

    return redirect()->back()->with('error', 'Log file not found.');
}

/**
 * Validate that the file path is within the logs directory and prevent directory traversal
 */
private function isValidLogFile($filePath, $logDirPath): bool
{
    if (empty($filePath)) {
        return false;
    }

    // Get the real path to prevent directory traversal attacks
    $realFilePath = realpath($filePath);
    $realLogDirPath = realpath($logDirPath);

    // If realpath returns false, the file doesn't exist or path is invalid
    if ($realFilePath === false || $realLogDirPath === false) {
        return false;
    }

    // Check if the file is within the logs directory
    if (strpos($realFilePath, $realLogDirPath) !== 0) {
        return false;
    }

    // Additional check: ensure it's a log file (laravel-*.log pattern)
    $fileName = basename($realFilePath);
    if (!preg_match('/^laravel-.*\.log$/', $fileName)) {
        return false;
    }

    return true;
}
}
