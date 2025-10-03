# Plesk Cron Job Setup for Auto-Reject System

## Problem
If the queue worker stops between cron runs, auto-reject jobs may be delayed by up to 1 minute.

## Solution: Multiple Cron Jobs

Set up these scheduled tasks in Plesk:

### Option A: Every 15 Seconds Coverage (Recommended)

**Task 1:**
- Schedule: `* * * * *` (every minute at :00)
- Command: `cd /var/www/vhosts/sare3.tld && php monitor-queue.php`

**Task 2:**
- Schedule: `* * * * *` (every minute at :15)
- Command: `sleep 15 && cd /var/www/vhosts/sare3.tld && php monitor-queue.php`

**Task 3:**
- Schedule: `* * * * *` (every minute at :30)
- Command: `sleep 30 && cd /var/www/vhosts/sare3.tld && php monitor-queue.php`

**Task 4:**
- Schedule: `* * * * *` (every minute at :45)
- Command: `sleep 45 && cd /var/www/vhosts/sare3.tld && php monitor-queue.php`

### Option B: Every 10 Seconds Coverage

**Task 1:** `* * * * *` → `cd /var/www/vhosts/sare3.tld && php monitor-queue.php`
**Task 2:** `* * * * *` → `sleep 10 && cd /var/www/vhosts/sare3.tld && php monitor-queue.php`
**Task 3:** `* * * * *` → `sleep 20 && cd /var/www/vhosts/sare3.tld && php monitor-queue.php`
**Task 4:** `* * * * *` → `sleep 30 && cd /var/www/vhosts/sare3.tld && php monitor-queue.php`
**Task 5:** `* * * * *` → `sleep 40 && cd /var/www/vhosts/sare3.tld && php monitor-queue.php`
**Task 6:** `* * * * *` → `sleep 50 && cd /var/www/vhosts/sare3.tld && php monitor-queue.php`

## How It Works

1. **00:00** - Task 1 runs, checks/starts queue worker
2. **00:15** - Task 2 runs (after 15s sleep), checks queue worker
3. **00:30** - Task 3 runs (after 30s sleep), checks queue worker
4. **00:45** - Task 4 runs (after 45s sleep), checks queue worker
5. **01:00** - Cycle repeats

## Maximum Delay

- **With Option A**: Maximum 15 seconds delay
- **With Option B**: Maximum 10 seconds delay
- **Current setup**: Up to 60 seconds delay

## Alternative: Single Continuous Script

If you prefer one script that runs continuously:

**Schedule**: `@reboot`
**Command**: `/var/www/vhosts/sare3.tld/continuous-monitor.sh`
