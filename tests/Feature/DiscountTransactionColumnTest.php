<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DiscountTransactionColumnTest extends TestCase
{
    public function test_subscription_plans_table_has_discount_transaction_column(): void
    {
        if (!Schema::hasTable('subscription_plans')) {
            $this->markTestSkipped('subscription_plans table is not available in this environment.');
        }

        $this->assertTrue(
            Schema::hasColumn('subscription_plans', 'discount_transaction'),
            'Run migrations: php artisan migrate --force'
        );
    }

    public function test_consumer_premium_plans_table_has_discount_transaction_column(): void
    {
        if (!Schema::hasTable('consumer_premium_plans')) {
            $this->markTestSkipped('consumer_premium_plans table is not available in this environment.');
        }

        $this->assertTrue(
            Schema::hasColumn('consumer_premium_plans', 'discount_transaction'),
            'Run migrations: php artisan migrate --force'
        );
    }
}
