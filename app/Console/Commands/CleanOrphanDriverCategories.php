<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DriverProfileService;

class CleanOrphanDriverCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'driver:clean-orphan-categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up orphaned driver categories, service pricing, skills, and access records where driver does not exist or has not completed onboarding';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Cleaning orphan driver category and access records...');

        $purged = DriverProfileService::cleanAllOrphanDriverRecords();

        $this->info("Purged records:");
        $this->line("- Orphan categories (non-existent drivers): {$purged['categories']}");
        $this->line("- Orphan service pricing: {$purged['pricing']}");
        $this->line("- Orphan service skills: {$purged['skills']}");
        $this->line("- Orphan access tokens: {$purged['access']}");

        $this->info('Cleanup completed successfully.');
        return 0;
    }
}