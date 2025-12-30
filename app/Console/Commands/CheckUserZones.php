<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Zone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckUserZones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:check-zones {--fix : Assign users without zones to default zone}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check which users have zones assigned and fix missing assignments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== User Zone Assignment Check ===');
        $this->newLine();

        // Get statistics
        $totalUsers = User::count();
        $usersWithZones = User::whereNotNull('zone_id')->count();
        $usersWithoutZones = User::whereNull('zone_id')->count();
        
        $totalDrivers = User::where('role', 'driver')->count();
        $driversWithZones = User::where('role', 'driver')->whereNotNull('zone_id')->count();
        $driversWithoutZones = User::where('role', 'driver')->whereNull('zone_id')->count();

        $this->info("Total Users: {$totalUsers}");
        $this->info("  - With zones: {$usersWithZones}");
        $this->info("  - Without zones: {$usersWithoutZones}");
        $this->newLine();
        
        $this->info("Total Drivers: {$totalDrivers}");
        $this->info("  - With zones: {$driversWithZones}");
        $this->info("  - Without zones: {$driversWithoutZones}");
        $this->newLine();

        // Show users with zones
        if ($usersWithZones > 0) {
            $this->info("Users with zones:");
            $users = User::with('zone')
                ->whereNotNull('zone_id')
                ->limit(10)
                ->get();
                
            $rows = [];
            foreach ($users as $user) {
                $rows[] = [
                    $user->id,
                    $user->name,
                    $user->role ?? 'user',
                    $user->zone ? $user->zone->name : 'N/A',
                    $user->zone ? $user->zone->timezone : 'N/A',
                ];
            }
            
            $this->table(['ID', 'Name', 'Role', 'Zone', 'Timezone'], $rows);
            $this->newLine();
        }

        // Show users without zones
        if ($usersWithoutZones > 0) {
            $this->warn("⚠ {$usersWithoutZones} users don't have zones assigned!");
            $this->warn("This means they won't get proper timezone conversion!");
            $this->newLine();
            
            $usersNoZone = User::whereNull('zone_id')->limit(10)->get();
            $rows = [];
            foreach ($usersNoZone as $user) {
                $rows[] = [
                    $user->id,
                    $user->name,
                    $user->role ?? 'user',
                ];
            }
            
            $this->table(['ID', 'Name', 'Role'], $rows);
            $this->newLine();
            
            // Offer to fix
            if ($this->option('fix') || $this->confirm('Do you want to assign these users to a default zone?')) {
                $this->fixMissingZones();
            }
        } else {
            $this->success("✓ All users have zones assigned!");
        }

        return 0;
    }

    protected function fixMissingZones()
    {
        $zones = Zone::all();
        
        if ($zones->isEmpty()) {
            $this->error('No zones available. Please create a zone first.');
            return;
        }
        
        $this->info('Available zones:');
        foreach ($zones as $zone) {
            $this->line("  {$zone->id}. {$zone->name} ({$zone->timezone})");
        }
        $this->newLine();
        
        $zoneId = $this->ask('Enter the zone ID to assign to users without zones');
        
        $zone = Zone::find($zoneId);
        if (!$zone) {
            $this->error("Zone {$zoneId} not found.");
            return;
        }
        
        $usersUpdated = User::whereNull('zone_id')->update(['zone_id' => $zoneId]);
        
        $this->success("✓ Updated {$usersUpdated} users to zone: {$zone->name}");
    }

    protected function success($message)
    {
        $this->info($message);
    }
}
