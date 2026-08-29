@extends('layouts.app')

@section('content')

<div class="page-wrapper">

    <div class="row page-titles">

        <div class="col-md-5 align-self-center">

            <h3 class="text-themecolor">System Logs</h3>

        </div>

        <div class="col-md-7 align-self-center">

            <ol class="breadcrumb">

                <li class="breadcrumb-item">

                    <a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a>

                </li>

                <li class="breadcrumb-item active">

                    System Logs

                </li>

            </ol>

        </div>

    </div>

    <div class="container-fluid">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row">

            <div class="col-12">

                <div class="card">

                    <div class="card-body">

                        <div class="userlist-topsearch d-flex mb-3">

                            <div class="userlist-top-left">
                                <a class="btn btn-danger btn-flat" href="{{ route('logs.clear') }}" onclick="return confirm('Are you sure you want to clear the log file?');">
                                    <i class="fa fa-trash mr-2"></i>Clear Log File
                                </a>
                            </div>

                        </div>

                        <div class="table-responsive m-t-10">

                            <table id="logsTable" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th style="width: 15%">Timestamp</th>
                                        <th style="width: 10%">Environment</th>
                                        <th style="width: 10%">Level</th>
                                        <th style="width: 65%">Message</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($logs) > 0)
                                        @foreach($logs as $log)
                                            <tr>
                                                <td>{{ $log['timestamp'] }}</td>
                                                <td><span class="badge badge-secondary">{{ strtoupper($log['env']) }}</span></td>
                                                <td>
                                                    @php
                                                        $level = strtoupper($log['level']);
                                                        $badgeClass = 'badge-secondary';
                                                        if (in_array($level, ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'])) {
                                                            $badgeClass = 'badge-danger';
                                                        } elseif (in_array($level, ['WARNING', 'NOTICE'])) {
                                                            $badgeClass = 'badge-warning';
                                                        } elseif (in_array($level, ['INFO', 'DEBUG'])) {
                                                            $badgeClass = 'badge-info';
                                                        }
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">{{ $level }}</span>
                                                </td>
                                                <td>
                                                    <div style="max-height: 100px; overflow-y: auto; white-space: pre-wrap; font-family: monospace; font-size: 12px;">
                                                        {{ $log['message'] }}
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" align="center">No logs found.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection

@section('scripts')

<script type="text/javascript">
    $(document).ready(function () {
        $(".shadow-sm").hide();
    });
</script>

@endsection
