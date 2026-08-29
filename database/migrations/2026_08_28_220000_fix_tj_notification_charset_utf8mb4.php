<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * CRITICAL FIX: tj_notification table uses CHARSET=utf8mb3 (MySQL's 3-byte UTF-8).
 *
 * Inserting 4-byte emoji characters (e.g. 🚖, 🎁) into any column of this table
 * causes:
 *   SQLSTATE[HY000]: General error: 3988
 *   Conversion from collation utf8mb4_unicode_ci into utf8mb3_general_ci impossible
 *
 * This breaks the entire ride booking flow because:
 *   1. The ride INSERT into tj_requete succeeds.
 *   2. The notification INSERT fails with the above error.
 *   3. The outer try/catch returns "Booking failed" to the app.
 *   4. The app stays on the booking page (thinks booking failed).
 *   5. The ride record IS already in the DB with status 'new'.
 *   6. Next booking attempt is blocked by "already on an active ride" check.
 *
 * Fix: Convert tj_notification to utf8mb4_unicode_ci.
 */
class FixTjNotificationCharsetUtf8mb4 extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tj_notification')) {
            DB::statement('ALTER TABLE tj_notification CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        }
    }

    public function down()
    {
        if (Schema::hasTable('tj_notification')) {
            DB::statement('ALTER TABLE tj_notification CONVERT TO CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci');
        }
    }
}
