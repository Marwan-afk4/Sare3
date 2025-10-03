#!/bin/bash

# Queue Worker Keep-Alive Script
PROJECT_PATH="/var/www/vhosts/sare3.tld"
cd $PROJECT_PATH

# Function to check if queue worker is running
is_running() {
    pgrep -f "queue:work" > /dev/null
}

# Function to start queue worker
start_worker() {
    echo "$(date): Starting queue worker..."
    nohup php artisan queue:work --sleep=3 --tries=3 --max-time=3600 >> storage/logs/queue.log 2>&1 &
    echo "$(date): Queue worker started"
}

# Start the worker if not running
if ! is_running; then
    start_worker
else
    echo "$(date): Queue worker is already running"
fi
