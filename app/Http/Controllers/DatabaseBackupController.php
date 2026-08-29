<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class DatabaseBackupController extends Controller
{
    /**
     * Master configuration & catalog tables protected from accidental deletion.
     */
    private array $protectedTables = [
        'users',                   // Super Admin & staff logins
        'roles',                   // ACL Roles
        'permissions',             // ACL Permissions
        'model_has_roles',
        'model_has_permissions',
        'role_has_permissions',
        'migrations',              // Laravel migrations
        'tj_currency',             // Currency configuration
        'currencies',
        'tj_settings',             // System settings
        'settings',
        'tj_commission',           // Commission rates
        'commission',
        'tj_payment_method',       // Payment gateway config
        'email_template',          // Email templates
        'email_templates',
        'tj_type_vehicule',        // Vehicle types
        'brands',                  // Vehicle brands
        'car_model',               // Car models catalog
        'banners',                 // App banners
        'consumer_premium_plans',  // Consumer plan definitions
        'subscription_plans',      // Business plan definitions
        'marketplace_categories',  // Product catalog categories
        'marketplace_products',    // Product catalog items
        'services',                // Home service catalog
        'service_categories',      // Home service categories
        'admin_documents',         // Required KYC documents config
        'admin_notification',      // Notification templates
        'api_key_settings',        // API keys configuration
        'app_version_controls',    // App version management
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display Database Backup, Restore & Data Management interface.
     */
    public function index()
    {
        $dbName = config('database.connections.mysql.database');
        
        // Fetch list of tables and row counts
        $tables = DB::select('SHOW TABLES');
        $tableKey = 'Tables_in_' . $dbName;
        
        $tableStats = [];
        $totalRows = 0;
        foreach ($tables as $t) {
            $tableName = $t->$tableKey ?? current((array)$t);
            $count = DB::table($tableName)->count();
            $tableStats[] = [
                'name'         => $tableName,
                'count'        => $count,
                'is_protected' => in_array($tableName, $this->protectedTables),
            ];
            $totalRows += $count;
        }

        return view('settings.backup_restore', compact('dbName', 'tableStats', 'totalRows'));
    }

    /**
     * Generate and stream full MySQL database dump as .sql file.
     */
    public function downloadBackup(Request $request)
    {
        $dbName = config('database.connections.mysql.database');
        $filename = "fiinway_db_backup_" . date('Y_m_d_His') . ".sql";

        return response()->streamDownload(function () use ($dbName) {
            $out = fopen('php://output', 'w');

            fwrite($out, "-- ========================================================\n");
            fwrite($out, "-- FIINWAY AUTOMATED DATABASE BACKUP DUMP\n");
            fwrite($out, "-- Database: {$dbName}\n");
            fwrite($out, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
            fwrite($out, "-- ========================================================\n\n");
            fwrite($out, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($out, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n");
            fwrite($out, "SET time_zone = '+00:00';\n\n");

            $tables = DB::select('SHOW TABLES');
            $tableKey = 'Tables_in_' . $dbName;

            foreach ($tables as $t) {
                $tableName = $t->$tableKey ?? current((array)$t);

                fwrite($out, "\n-- --------------------------------------------------------\n");
                fwrite($out, "-- Table structure for table `{$tableName}`\n");
                fwrite($out, "-- --------------------------------------------------------\n\n");
                fwrite($out, "DROP TABLE IF EXISTS `{$tableName}`;\n");

                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                if (!empty($createTable)) {
                    $createSql = $createTable[0]->{'Create Table'} ?? current((array)$createTable[0]);
                    fwrite($out, $createSql . ";\n\n");
                }

                // Dump table rows
                $rows = DB::table($tableName)->get();
                if ($rows->count() > 0) {
                    fwrite($out, "-- Dumping data for table `{$tableName}`\n");
                    foreach ($rows->chunk(100) as $chunk) {
                        fwrite($out, "INSERT INTO `{$tableName}` VALUES \n");
                        $valuesArr = [];
                        foreach ($chunk as $row) {
                            $escapedValues = array_map(function ($val) {
                                if (is_null($val)) {
                                    return 'NULL';
                                }
                                return "'" . addslashes((string)$val) . "'";
                            }, (array)$row);
                            $valuesArr[] = "(" . implode(", ", $escapedValues) . ")";
                        }
                        fwrite($out, implode(",\n", $valuesArr) . ";\n");
                    }
                    fwrite($out, "\n");
                }
            }

            fwrite($out, "SET FOREIGN_KEY_CHECKS=1;\n");
            fwrite($out, "-- --- End of Backup Dump ---\n");
            fclose($out);
        }, $filename, [
            'Content-Type'        => 'application/sql',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Restore database from pasted SQL or uploaded .sql file.
     */
    public function restoreSql(Request $request)
    {
        $request->validate([
            'sql_file' => 'nullable|file|max:51200', // 50MB max
            'sql_text' => 'nullable|string',
        ]);

        $sqlContent = '';

        if ($request->hasFile('sql_file')) {
            $file = $request->file('sql_file');
            $sqlContent = file_get_contents($file->getRealPath());
        } elseif ($request->filled('sql_text')) {
            $sqlContent = $request->input('sql_text');
        }

        if (empty(trim($sqlContent))) {
            return redirect()->back()->with('error', 'Please provide SQL content either by uploading a .sql file or pasting queries into the box.');
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Split SQL into individual statements
            $queries = $this->splitSqlQueries($sqlContent);
            $successCount = 0;

            foreach ($queries as $query) {
                $trimmed = trim($query);
                if (!empty($trimmed) && !str_starts_with($trimmed, '--') && !str_starts_with($trimmed, '/*')) {
                    DB::unprepared($trimmed);
                    $successCount++;
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return redirect()->back()->with('success', "Database successfully restored! {$successCount} SQL statements executed successfully.");
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            Log::error('SQL Restore Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error restoring database: ' . $e->getMessage());
        }
    }

    /**
     * Truncate / Delete all rows from an individual table.
     */
    public function truncateTable(Request $request)
    {
        $request->validate([
            'table_name' => 'required|string',
        ]);

        $tableName = $request->input('table_name');

        if (!Schema::hasTable($tableName)) {
            return redirect()->back()->with('error', "Table `{$tableName}` does not exist in the database.");
        }

        if (in_array($tableName, $this->protectedTables)) {
            return redirect()->back()->with('error', "Table `{$tableName}` is a protected core system/catalog table and cannot be truncated.");
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table($tableName)->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return redirect()->back()->with('success', "Table `{$tableName}` has been cleared / truncated successfully.");
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return redirect()->back()->with('error', "Failed to truncate table `{$tableName}`: " . $e->getMessage());
        }
    }

    /**
     * Purge ONLY Users & Transactions:
     * - Clears test app users, drivers, vehicles, and temporary user auth data.
     * - Clears transactions, rides, service bookings, marketplace orders, subscriptions, referrals, withdrawals, and medical claims.
     * - Leaves admin logins, product catalogs, brand/car models, plans, settings, and permissions 100% untouched.
     */
    public function purgeTestData(Request $request)
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $userAndTransactionTables = [
                // 1. Users & Drivers
                'tj_user_app',
                'tj_conducteur',
                'tj_vehicule',
                'common_user_base',
                'auth_otp_temp',
                'adduser',

                // 2. Transactions, Orders & Activity
                'tj_transaction',
                'tj_conducteur_transaction',
                'tj_requete',
                'service_requests',
                'marketplace_order_items',
                'marketplace_orders',
                'subscription_history',
                'referral',
                'withdrawals',
                'tj_medical_claims',
                'tj_medical_cards',
                'parcel_orders',
                'tj_note',
                'tj_sos',
            ];

            $clearedCount = 0;
            foreach ($userAndTransactionTables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                    $clearedCount++;
                }
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return redirect()->back()->with('success', "Only test users and transactions were purged! ({$clearedCount} tables cleared). All catalogs, plans, settings, and admin logins remain intact.");
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return redirect()->back()->with('error', 'Purge failed: ' . $e->getMessage());
        }
    }

    /**
     * Helper to split multiple SQL statements cleanly.
     */
    private function splitSqlQueries(string $sql): array
    {
        $lines = explode("\n", $sql);
        $queries = [];
        $query = '';

        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            if (empty($trimmedLine) || str_starts_with($trimmedLine, '--') || str_starts_with($trimmedLine, '/*')) {
                continue;
            }

            $query .= $line . "\n";
            if (str_ends_with($trimmedLine, ';')) {
                $queries[] = $query;
                $query = '';
            }
        }

        if (!empty(trim($query))) {
            $queries[] = $query;
        }

        return $queries;
    }
}
