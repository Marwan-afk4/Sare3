# Auto-Reject Ride System

This system automatically rejects rides if drivers don't respond within a specified time limit (default: 15 seconds).

## How It Works

1. **Initial Assignment**: When a ride is created and assigned to a driver, an `AutoRejectRideJob` is scheduled to run after 15 seconds.

2. **Driver Response**: If the driver accepts or rejects the ride before the timeout, the job will detect this and do nothing.

3. **Auto-Rejection**: If no response is received within 15 seconds:
   - The driver is added to the `rejected_drivers` list
   - The ride's `driver_id` is reset to `null`
   - The system searches for an alternative driver
   - If found, a new auto-reject job is scheduled for the new driver
   - The process repeats until a driver accepts or no more drivers are available

4. **Firebase Sync**: All changes are synchronized with Firebase in real-time.

## Configuration

You can configure the timeout in your `.env` file:

```env
RIDE_AUTO_REJECT_TIMEOUT=15
RIDE_MAX_DRIVER_REJECTIONS=10
```

Or modify the `config/ride.php` file directly.

## Database Changes

A new column `auto_rejected_at` has been added to the `rides` table to track when rides were auto-rejected.

## Queue Requirements

This system requires Laravel's queue system to be running:

```bash
php artisan queue:work
```

For production, use a process manager like Supervisor to keep the queue worker running.

## Testing

You can test the system using the provided command:

```bash
php artisan test:auto-reject {ride_id}
```

This will dispatch an auto-reject job with a 5-second delay for testing purposes.

## Files Modified/Created

### New Files:
- `app/Jobs/AutoRejectRideJob.php` - The main job that handles auto-rejection
- `app/Console/Commands/TestAutoRejectJob.php` - Testing command
- `config/ride.php` - Configuration file
- `database/migrations/2025_10_03_202902_add_auto_rejected_at_to_rides_table.php` - Migration

### Modified Files:
- `app/Http/Controllers/Api/User/RideEstimateController.php` - Added auto-reject job scheduling
- `app/Http/Controllers/Api/Driver/RideActionsController.php` - Added auto-reject job scheduling for reassignments

## Flow Diagram

```
User Creates Ride → Driver Assigned → Auto-Reject Job Scheduled (15s)
                                    ↓
                              Driver Responds?
                                    ↓
                            Yes → Job Does Nothing
                                    ↓
                            No → Auto-Reject → Search Alternative Driver
                                                        ↓
                                              Found? → Assign & Schedule New Job
                                                        ↓
                                              No → Ride Remains Pending
```

## Important Notes

1. The job uses the ride's `updated_at` timestamp to prevent duplicate executions
2. Only rides in 'pending' status with the correct driver assignment are auto-rejected
3. The system respects the existing `rejected_drivers` list to avoid reassigning to previously rejected drivers
4. All Firebase updates include proper error handling
5. Comprehensive logging is included for debugging and monitoring
