#!/bin/bash

# Continuous Queue Worker Monitor
# This script runs continuously and checks every 10 seconds

PROJECT_PATH="/var/www/vhosts/sare3.tld"
LOG_FILE="$PROJECT_PATH/storage/logs/continuous-monitor.log"
PID_FILE="$PROJECT_PATH/storage/logs/queue-worker.pid"

cd $PROJECT_PATH

log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> $LOG_FILE
}

is_queue_worker_running() {
    if [ -f "$PID_FILE" ]; then
        PID=$(cat $PID_FILE)
        if kill -0 $PID 2>/dev/null; then
            return 0  # Running
        else
            rm -f $PID_FILE  # Remove stale PID file
        fi
    fi

    # Fallback check
    pgrep -f "queue:work" > /dev/null
}

start_queue_worker() {
    log_message "Starting queue worker..."
    nohup php artisan queue:work --sleep=1 --tries=3 --max-time=1800 >> storage/logs/queue.log 2>&1 &
    echo $! > $PID_FILE
    log_message "Queue worker started with PID: $(cat $PID_FILE)"
}

get_jobs_count() {
    php artisan tinker --execute="echo DB::table('jobs')->count();" 2>/dev/null | tail -1
}

log_message "Continuous monitor started"

# Main monitoring loop
while true; do
    if ! is_queue_worker_running; then
        log_message "Queue worker not running, starting..."
        start_queue_worker
        sleep 3  # Give it time to start

        if is_queue_worker_running; then
            JOBS_COUNT=$(get_jobs_count)
            log_message "Queue worker started successfully. Jobs in queue: $JOBS_COUNT"
        else
            log_message "Failed to start queue worker"
        fi
    else
        JOBS_COUNT=$(get_jobs_count)
        if [ "$JOBS_COUNT" -gt 0 ]; then
            log_message "Queue worker running. Processing $JOBS_COUNT jobs"
        fi
    fi

    # Wait 10 seconds before next check
    sleep 10
done
