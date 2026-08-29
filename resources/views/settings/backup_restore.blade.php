@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif;">
    <div class="container-fluid py-4 px-3 px-lg-4">

        <!-- Header -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4 pb-2 border-bottom">
            <div>
                <h2 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0;">Database Backup, Restore & Data Management</h2>
                <p style="font-size: 13px; font-weight: 600; color: #64748b; margin: 4px 0 0;">Download SQL backups, restore raw SQL statements, or purge test users and clear individual tables</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('database-backup.download') }}" class="btn btn-success font-weight-700 px-3 py-2" style="border-radius: 10px; font-size: 13px;">
                    <i class="mdi mdi-download mr-1"></i> Download Full SQL Backup
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show font-weight-700" role="alert" style="border-radius: 12px;">
            <i class="mdi mdi-check-circle-outline mr-2 font-18"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show font-weight-700" role="alert" style="border-radius: 12px;">
            <i class="mdi mdi-alert-circle-outline mr-2 font-18"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <!-- 3 Overview Tiles -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="p-3 bg-white border rounded" style="box-shadow: 0 2px 6px rgba(0,0,0,0.04); border-radius: 14px !important;">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted font-11 font-weight-700 text-uppercase">DATABASE NAME</span>
                        <i class="mdi mdi-database text-primary font-20"></i>
                    </div>
                    <div class="font-20 font-weight-800 text-dark">{{ $dbName }}</div>
                    <div class="text-muted font-12 font-weight-600">MySQL Database Instance</div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="p-3 bg-white border rounded" style="box-shadow: 0 2px 6px rgba(0,0,0,0.04); border-radius: 14px !important;">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted font-11 font-weight-700 text-uppercase">TOTAL TABLES</span>
                        <i class="mdi mdi-table text-info font-20"></i>
                    </div>
                    <div class="font-20 font-weight-800 text-dark">{{ count($tableStats) }} Tables</div>
                    <div class="text-muted font-12 font-weight-600">Active schemas tracked</div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="p-3 bg-white border rounded" style="box-shadow: 0 2px 6px rgba(0,0,0,0.04); border-radius: 14px !important;">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted font-11 font-weight-700 text-uppercase">TOTAL DATA RECORDS</span>
                        <i class="mdi mdi-format-list-numbered text-success font-20"></i>
                    </div>
                    <div class="font-20 font-weight-800 text-success">{{ number_format($totalRows) }} Rows</div>
                    <div class="text-muted font-12 font-weight-600">Across all system tables</div>
                </div>
            </div>
        </div>

        <!-- Master Purge Action Banner -->
        <div class="card p-3 mb-4 border" style="border-radius: 16px; background: #fff1f2; border-color: #fecdd3 !important;">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <h5 class="font-weight-800 font-16 text-danger mb-1">
                        <i class="mdi mdi-delete-sweep mr-1"></i> Delete ONLY Users & Transactions
                    </h5>
                    <p class="text-dark font-13 font-weight-600 mb-0">
                        Purges <strong>ONLY</strong> test app users, drivers, vehicles, bookings, wallet transactions, and orders.
                    </p>
                    <small class="text-muted font-12 font-weight-600">
                        <i class="mdi mdi-shield-check text-success"></i> <strong>Safe:</strong> Admin logins, plans catalog, vehicle brands/models, products catalog, and settings are 100% preserved.
                    </small>
                </div>
                <form action="{{ route('database-backup.purge-test-data') }}" method="POST" onsubmit="return confirm('⚠️ Are you sure you want to delete ONLY users and transactions? This will reset all customer wallets, bookings, and earnings to zero while keeping all catalogs, plans, and settings intact.');">
                    @csrf
                    <button type="submit" class="btn btn-danger font-weight-800 px-4 py-2" style="border-radius: 10px; font-size: 13px; white-space: nowrap;">
                        <i class="mdi mdi-delete-forever mr-1"></i> Delete Users & Transactions
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Form Grid -->
        <div class="row">
            <!-- Left: SQL Restore Form (Paste / Upload) -->
            <div class="col-12 col-lg-7 mb-4">
                <div class="card p-4 border" style="border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.04);">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h4 class="font-weight-800 font-18 text-dark mb-0">
                            <i class="mdi mdi-database-import text-purple mr-1"></i> Restore / Execute SQL Script
                        </h4>
                        <span class="badge badge-primary font-12 font-weight-700">Raw SQL Runner</span>
                    </div>

                    <form action="{{ route('database-backup.restore') }}" method="POST" enctype="multipart/form-data" onsubmit="return confirm('⚠️ Are you sure you want to execute these SQL queries? This will modify data in your database.');">
                        @csrf

                        <!-- Option A: File Upload -->
                        <div class="form-group mb-3">
                            <label class="font-weight-700 font-13 text-dark">Option 1: Upload SQL Backup File (.sql)</label>
                            <input type="file" name="sql_file" class="form-control" accept=".sql" style="border-radius: 10px; height: 42px;">
                            <small class="text-muted font-12">Select an exported .sql dump file from your computer (Max: 50MB)</small>
                        </div>

                        <div class="d-flex align-items-center my-3">
                            <hr class="flex-grow-1">
                            <span class="px-3 text-muted font-12 font-weight-800">OR PASTE SQL QUERIES BELOW</span>
                            <hr class="flex-grow-1">
                        </div>

                        <!-- Option B: Paste Text Area -->
                        <div class="form-group mb-3">
                            <label class="font-weight-700 font-13 text-dark">Option 2: Paste Raw SQL Statements</label>
                            <textarea name="sql_text" rows="12" class="form-control font-monospace" placeholder="Paste your SQL INSERT, UPDATE, or table dump queries here...&#10;&#10;Example:&#10;INSERT INTO `tj_user_app` (`id`, `prenom`, `nom`, `phone`, `amount`) VALUES (1, 'John', 'Doe', '+919999999999', 500);&#10;" style="border-radius: 12px; font-size: 13px; font-family: monospace; background: #0f172a; color: #38bdf8;"></textarea>
                        </div>

                        <div class="p-3 mb-3 rounded" style="background: #fffbeb; border: 1px solid #fef3c7;">
                            <div class="font-weight-700 text-warning font-13 mb-1"><i class="mdi mdi-alert-outline mr-1"></i> Safety Notes:</div>
                            <ul class="text-dark font-12 mb-0 pl-3">
                                <li>Foreign key validation is temporarily disabled during execution to allow relational restores without order conflicts.</li>
                                <li>Ensure valid SQL syntax terminating with semicolons (<code>;</code>).</li>
                            </ul>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block py-2 font-weight-800 font-14" style="border-radius: 10px; background: #0f172a; border-color: #0f172a;">
                            <i class="mdi mdi-play-circle mr-1"></i> Execute SQL & Restore Database
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right: Backup Card & Table Inventory with Delete Buttons -->
            <div class="col-12 col-lg-5 mb-4">
                <!-- Backup Action Card -->
                <div class="card p-3 mb-4 border" style="border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.04);">
                    <h5 class="font-weight-800 font-16 text-dark mb-2"><i class="mdi mdi-cloud-download text-success mr-1"></i> Create Full Backup</h5>
                    <p class="text-muted font-12 font-weight-600 mb-3">
                        Export complete database tables, schemas, and live data records into a structured SQL file for safe storage or migration.
                    </p>
                    <a href="{{ route('database-backup.download') }}" class="btn btn-success btn-block py-2 font-weight-800 font-14" style="border-radius: 10px;">
                        <i class="mdi mdi-download mr-1"></i> Download .SQL Dump
                    </a>
                </div>

                <!-- Database Tables List with Individual Delete/Truncate Buttons -->
                <div class="card p-3 border" style="border-radius: 16px; max-height: 600px; overflow-y: auto;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="font-weight-800 font-15 text-dark mb-0">Tables Inventory & Actions</h6>
                        <span class="badge badge-light font-11 font-weight-700">{{ count($tableStats) }} Tables</span>
                    </div>

                    <!-- Search filter -->
                    <div class="mb-3">
                        <input type="text" id="tableSearchInput" class="form-control form-control-sm font-weight-600" placeholder="🔍 Filter table name..." onkeyup="filterTables()">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm mb-0 font-12 align-middle" id="inventoryTable">
                            <thead>
                                <tr class="text-muted font-weight-700">
                                    <th>Table Name</th>
                                    <th class="text-center">Rows</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tableStats as $ts)
                                <tr class="table-row-item">
                                    <td class="font-weight-700 text-dark table-name-col">{{ $ts['name'] }}</td>
                                    <td class="text-center font-weight-700 {{ $ts['count'] > 0 ? 'text-primary' : 'text-muted' }}">
                                        {{ number_format($ts['count']) }}
                                    </td>
                                    <td class="text-right">
                                        @if($ts['is_protected'])
                                            <span class="badge badge-light text-muted font-11 font-weight-600" title="Core System Table (Protected)"><i class="mdi mdi-lock-outline"></i> System</span>
                                        @else
                                            <form action="{{ route('database-backup.truncate-table') }}" method="POST" class="d-inline" onsubmit="return confirm('⚠️ Are you sure you want to clear/delete all data from `{{ $ts['name'] }}`?');">
                                                @csrf
                                                <input type="hidden" name="table_name" value="{{ $ts['name'] }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2 font-11 font-weight-700" style="border-radius: 6px;" title="Clear table data">
                                                    <i class="mdi mdi-delete-outline"></i> Clear
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function filterTables() {
    var input = document.getElementById('tableSearchInput');
    var filter = input.value.toLowerCase();
    var rows = document.querySelectorAll('#inventoryTable .table-row-item');

    rows.forEach(function(row) {
        var tableName = row.querySelector('.table-name-col').textContent.toLowerCase();
        if (tableName.indexOf(filter) > -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
@endsection
