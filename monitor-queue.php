<?php

// Queue Worker Monitor Script
// This script ensures the queue worker is always running

$projectPath = '/var/www/vhosts/sare3.tld';
$logFile = $projectPath.'/storage/logs/queue-monitor.log';

function logMessage($message)
{
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

function isQueueWorkerRunning()
{
    $output = shell_exec('pgrep -f "queue:work"');

    return ! empty(trim($output));
}

function startQueueWorker()
{
    global $projectPath;
    $command = "cd $projectPath && nohup php artisan queue:work --sleep=3 --tries=3 --max-time=3600 > storage/logs/queue.log 2>&1 &";
    shell_exec($command);
    logMessage('Queue worker started');
}

// Check if queue worker is running
if (! isQueueWorkerRunning()) {
    logMessage('Queue worker not found, starting...');
    startQueueWorker();
    sleep(2); // Give it time to start

    if (isQueueWorkerRunning()) {
        logMessage('Queue worker successfully started');
    } else {
        logMessage('Failed to start queue worker');
    }
} else {
    logMessage('Queue worker is already running');
}
