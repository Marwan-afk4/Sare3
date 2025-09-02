<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

class CheckTokens extends Command
{
    protected $signature = 'tokens:check';
    protected $description = 'Check active tokens in the system';

    public function handle()
    {
        $tokens = PersonalAccessToken::with('tokenable')->get();
        
        $this->info("Total active tokens: " . $tokens->count());
        
        $this->table(
            ['ID', 'User ID', 'User Role', 'Name', 'Created', 'Last Used'],
            $tokens->map(function ($token) {
                return [
                    $token->id,
                    $token->tokenable_id,
                    $token->tokenable->role ?? 'N/A',
                    $token->name,
                    $token->created_at->format('Y-m-d H:i:s'),
                    $token->last_used_at ? $token->last_used_at->format('Y-m-d H:i:s') : 'Never'
                ];
            })
        );
    }
}