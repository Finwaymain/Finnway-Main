<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('upi_qr_transactions')) {
            Schema::create('upi_qr_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('razorpay_payment_id', 100)->unique();
                $table->string('razorpay_order_id', 100)->nullable();
                $table->string('ac_no', 50)->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('user_type', 20)->default('customer'); // 'customer' or 'driver'
                $table->decimal('amount', 12, 2);
                $table->decimal('fee', 10, 2)->default(0);
                $table->decimal('tax', 10, 2)->default(0);
                $table->string('payer_name')->nullable();
                $table->string('payer_vpa')->nullable();
                $table->string('payer_phone')->nullable();
                $table->string('payment_method', 50)->default('UPI');
                $table->string('status', 30)->default('captured');
                $table->boolean('wallet_credited')->default(true);
                $table->text('raw_payload')->nullable();
                $table->timestamps();
            });
        }

        // Add counterparty column to tj_transaction and tj_conducteur_transaction if not present
        if (Schema::hasTable('tj_transaction')) {
            if (!Schema::hasColumn('tj_transaction', 'counterparty')) {
                Schema::table('tj_transaction', function (Blueprint $table) {
                    $table->string('counterparty')->nullable()->after('payment_method');
                });
            }
        }

        if (Schema::hasTable('tj_conducteur_transaction')) {
            if (!Schema::hasColumn('tj_conducteur_transaction', 'counterparty')) {
                Schema::table('tj_conducteur_transaction', function (Blueprint $table) {
                    $table->string('counterparty')->nullable()->after('payment_method');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('upi_qr_transactions');

        if (Schema::hasTable('tj_transaction') && Schema::hasColumn('tj_transaction', 'counterparty')) {
            Schema::table('tj_transaction', function (Blueprint $table) {
                $table->dropColumn('counterparty');
            });
        }

        if (Schema::hasTable('tj_conducteur_transaction') && Schema::hasColumn('tj_conducteur_transaction', 'counterparty')) {
            Schema::table('tj_conducteur_transaction', function (Blueprint $table) {
                $table->dropColumn('counterparty');
            });
        }
    }
};
