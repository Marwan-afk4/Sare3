<?php

// Enhanced Queue Worker Monitor Script
// This script ensures the queue worker is always running with better reliability

$projectPath = '/var/www/vhosts/sare3.tld';
$logFile = $projectPath.'/storage/logs/queue-monitor.log';
$pidFile = $projectPath.'/storage/logs/queue-worker.pid';

function logMessage($message)
{
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

function isQueueWorkerRunning()
{
    global $pidFile;

    // Check if PID file exists and process is running
    if (file_exists($pidFile)) {
        $pid = trim(file_get_contents($pidFile));
        if ($pid && posix_kill($pid, 0)) {
            return true;
        } else {
            // PID file exists but process is dead, remove it
            unlink($pidFile);
        }
    }

    // Fallback: check by process name
    $output = shell_exec('pgrep -f "queue:work"');

    return ! empty(trim($output));
}

function startQueueWorker()
{
    global $projectPath, $pidFile;

    $command = "cd $projectPath && nohup php artisan queue:work --sleep=1 --tries=3 --max-time=1800 > storage/logs/queue.log 2>&1 & echo \$! > $pidFile";
    shell_exec($command);
    logMessage("Queue worker started with PID file: $pidFile");
}

function getQueueJobsCount()
{
    global $projectPath;
    $command = "cd $projectPath && php artisan tinker --execute=\"echo DB::table('jobs')->count();\" 2>/dev/null";
    $output = shell_exec($command);

    return (int) trim($output);
}

// Check if queue worker is running
if (! isQueueWorkerRunning()) {
    logMessage('Queue worker not found, starting...');
    startQueueWorker();
    sleep(2); // Give it time to start

    if (isQueueWorkerRunning()) {
        $jobsCount = getQueueJobsCount();
        logMessage("Queue worker successfully started. Jobs in queue: $jobsCount");
    } else {
        logMessage('Failed to start queue worker');
    }
} else {
    $jobsCount = getQueueJobsCount();
    if ($jobsCount > 0) {
        logMessage("Queue worker running. Processing $jobsCount jobs");
    }
}
