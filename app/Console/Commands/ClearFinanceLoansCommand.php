<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearFinanceLoansCommand extends Command
{
    protected $signature = 'finance:clear-loans {--all : Also clear documents, wallets, transactions, and customers}';
    protected $description = 'Remove all data from finance loan applications and related tables for a fresh start';

    public function handle()
    {
        $this->info('Cleaning finance loan tables...');
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        DB::table('finance_loan_applications')->truncate();
        DB::table('finance_daily_schedules')->truncate();
        DB::table('finance_document_requests')->truncate();
        DB::table('finance_documents')->truncate();
        DB::table('finance_transactions')->truncate();
        DB::table('finance_wallets')->truncate();

        $this->line('✓ Cleared finance_loan_applications');
        $this->line('✓ Cleared finance_daily_schedules');
        $this->line('✓ Cleared finance_document_requests');
        $this->line('✓ Cleared finance_documents');
        $this->line('✓ Cleared finance_transactions');
        $this->line('✓ Cleared finance_wallets');

        if ($this->option('all')) {
            DB::table('finance_customers')->truncate();
            $this->line('✓ Cleared finance_customers');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        $this->info('All loan application data removed successfully! You now have a clean slate.');
        return 0;
    }
}
