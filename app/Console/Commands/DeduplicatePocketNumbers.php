<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PocketNumberService;

class DeduplicatePocketNumbers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pocket:deduplicate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resolve and deduplicate pocket numbers across consumers and drivers so all users have unique pocket numbers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking and fixing pocket number collisions across customers and drivers...');

        $results = PocketNumberService::fixAllCollisions();

        if (empty($results)) {
            $this->info('All pocket numbers are already unique and non-colliding!');
        } else {
            $this->info('Resolved ' . count($results) . ' collisions/updates:');
            foreach ($results as $res) {
                $this->line("- [{$res['role']}] ID {$res['id']}: {$res['old_ac_no']} => {$res['new_ac_no']}");
            }
        }

        return 0;
    }
}
