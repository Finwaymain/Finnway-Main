<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearMarketplaceProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketplace:clear-products {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all products and product images from marketplace to start completely fresh';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->warn('Removing all products and product images from marketplace...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $prodCount = 0;
        $imgCount = 0;

        if (Schema::hasTable('marketplace_products')) {
            $prodCount = DB::table('marketplace_products')->count();
            DB::table('marketplace_products')->truncate();
        }

        if (Schema::hasTable('marketplace_product_images')) {
            $imgCount = DB::table('marketplace_product_images')->count();
            DB::table('marketplace_product_images')->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info("✔ Cleaned {$prodCount} products and {$imgCount} product images from marketplace.");
        $this->info("✔ Marketplace is now completely clean and fresh!");

        return Command::SUCCESS;
    }
}
