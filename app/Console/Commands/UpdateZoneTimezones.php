<?php

namespace App\Console\Commands;

use App\Models\Zone;
use Illuminate\Console\Command;

class UpdateZoneTimezones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zones:update-timezones 
                            {--zone= : Specific zone ID to update}
                            {--timezone= : Timezone to set}
                            {--all : Update all zones}
                            {--list : List all zones with their timezones}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update timezone settings for zones';

    protected $availableTimezones = [
        'Africa/Cairo' => 'Egypt (UTC+2)',
        'Asia/Amman' => 'Jordan (UTC+3)',
        'Asia/Riyadh' => 'Saudi Arabia (UTC+3)',
        'Asia/Dubai' => 'UAE (UTC+4)',
        'Asia/Kuwait' => 'Kuwait (UTC+3)',
        'Asia/Beirut' => 'Lebanon (UTC+2)',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // List mode
        if ($this->option('list')) {
            return $this->listZones();
        }

        // Update specific zone
        if ($this->option('zone') && $this->option('timezone')) {
            return $this->updateSpecificZone(
                $this->option('zone'),
                $this->option('timezone')
            );
        }

        // Interactive mode for all zones
        if ($this->option('all') || (!$this->option('zone') && !$this->option('timezone'))) {
            return $this->updateAllZonesInteractive();
        }

        $this->error('Invalid options. Use --list to see zones, or --zone and --timezone to update a specific zone.');
        return 1;
    }

    protected function listZones()
    {
        $zones = Zone::all();
        
        $this->info('=== Current Zones Configuration ===');
        $this->newLine();
        
        $headers = ['ID', 'Name', 'Timezone', 'Description'];
        $rows = [];
        
        foreach ($zones as $zone) {
            $tzDesc = $this->availableTimezones[$zone->timezone] ?? $zone->timezone;
            $rows[] = [
                $zone->id,
                $zone->name,
                $zone->timezone,
                $tzDesc
            ];
        }
        
        $this->table($headers, $rows);
        
        $this->newLine();
        $this->info('Available Timezones:');
        foreach ($this->availableTimezones as $tz => $desc) {
            $this->line("  - {$tz} => {$desc}");
        }
        
        $this->newLine();
        $this->info('To update a zone, use:');
        $this->line('  php artisan zones:update-timezones --zone=1 --timezone=Asia/Amman');
        $this->newLine();
        
        return 0;
    }

    protected function updateSpecificZone($zoneId, $timezone)
    {
        $zone = Zone::find($zoneId);
        
        if (!$zone) {
            $this->error("Zone with ID {$zoneId} not found.");
            return 1;
        }
        
        if (!array_key_exists($timezone, $this->availableTimezones)) {
            $this->error("Invalid timezone: {$timezone}");
            $this->info('Available timezones:');
            foreach ($this->availableTimezones as $tz => $desc) {
                $this->line("  - {$tz}");
            }
            return 1;
        }
        
        $zone->timezone = $timezone;
        $zone->save();
        
        $this->success("✓ Zone '{$zone->name}' updated to {$timezone} ({$this->availableTimezones[$timezone]})");
        
        return 0;
    }

    protected function updateAllZonesInteractive()
    {
        $zones = Zone::all();
        
        if ($zones->isEmpty()) {
            $this->error('No zones found in the database.');
            return 1;
        }
        
        $this->info('=== Update Zone Timezones (Interactive) ===');
        $this->newLine();
        
        foreach ($zones as $zone) {
            $this->line("Zone: {$zone->name} (ID: {$zone->id})");
            $this->line("Current timezone: {$zone->timezone}");
            $this->newLine();
            
            $choices = array_map(
                fn($tz, $desc) => "{$tz} - {$desc}",
                array_keys($this->availableTimezones),
                $this->availableTimezones
            );
            $choices[] = 'Skip (keep current)';
            
            $choice = $this->choice(
                'Select timezone for this zone',
                $choices,
                count($choices) - 1
            );
            
            if ($choice !== 'Skip (keep current)') {
                $timezone = explode(' - ', $choice)[0];
                $zone->timezone = $timezone;
                $zone->save();
                $this->success("✓ Updated to {$timezone}");
            } else {
                $this->info('Skipped');
            }
            
            $this->newLine();
        }
        
        $this->newLine();
        $this->success('Zone timezone update complete!');
        $this->newLine();
        
        // Show final configuration
        $this->listZones();
        
        return 0;
    }

    protected function success($message)
    {
        $this->info($message);
    }
}
