@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.payout_request')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.payout_request')}}</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-tabs customtab m-b-20" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link {{ ($selectedTab ?? 'all') == 'all' ? 'active' : '' }}" href="{{ url('/payoutRequest?tab=all') }}">
                                    All Requests
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ ($selectedTab ?? '') == 'driver' ? 'active' : '' }}" href="{{ url('/payoutRequest?tab=driver') }}">
                                    🏎️ Driver Requests
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ ($selectedTab ?? '') == 'user' ? 'active' : '' }}" href="{{ url('/payoutRequest?tab=user') }}">
                                    👤 Consumer / User Requests
                                </a>
                            </li>
                        </ul>

                        <div id="data-table_processing" class="dataTables_processing panel panel-default" style="display: none;">{{trans('lang.processing')}}</div>

                        <div class="table-responsive m-t-10">
                            <table id="example24" class="display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Name & Contact</th>
                                        <th>Bank Account Details</th>
                                        <th>{{trans('lang.paid_amount')}}</th>
                                        <th>{{trans('lang.drivers_payout_note')}}</th>
                                        <th>{{trans('lang.drivers_payout_paid_date')}}</th>
                                        <th>{{trans('lang.drivers_payout_status')}}</th>
                                        <th>{{trans('lang.action')}}</th>
                                    </tr>
                                </thead>
                                <tbody id="append_list1">
                                @if(count($withdrawal) > 0)
                                @foreach($withdrawal as $value)
                                    <tr>
                                        <td>
                                            @if(($value->user_category ?? '') == 'User')
                                                <span class="badge badge-info p-2"><i class="fa fa-user"></i> Consumer User</span>
                                            @else
                                                <span class="badge badge-primary p-2"><i class="fa fa-car"></i> Driver Partner</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong style="font-size:14px;">{{ $value->nom }} {{ $value->prenom }}</strong>
                                            @if(!empty($value->phone))
                                                <br><small class="text-muted"><i class="fa fa-phone"></i> {{ $value->phone }}</small>
                                            @endif
                                            @if(!empty($value->email))
                                                <br><small class="text-muted"><i class="fa fa-envelope"></i> {{ $value->email }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($value->bank_name) || !empty($value->account_no))
                                                <div style="font-size:12px; line-height:1.4;">
                                                    <strong>Bank:</strong> {{ $value->bank_name ?: 'N/A' }}<br>
                                                    @if(!empty($value->branch_name))
                                                        <strong>Branch:</strong> {{ $value->branch_name }}<br>
                                                    @endif
                                                    <strong>Acc No:</strong> <span class="font-weight-bold text-dark">{{ $value->account_no ?: 'N/A' }}</span><br>
                                                    <strong>Holder:</strong> {{ $value->holder_name ?: 'N/A' }}<br>
                                                    @if(!empty($value->ifsc_code))
                                                        <strong>IFSC Code:</strong> <span class="badge badge-secondary">{{ $value->ifsc_code }}</span><br>
                                                    @endif
                                                    @if(!empty($value->other_info))
                                                        <strong>Other Info:</strong> {{ $value->other_info }}
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-danger font-weight-bold"><i class="fa fa-exclamation-triangle"></i> No Bank Details</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="text-success" style="font-size:15px;">
                                            @if($currency->symbol_at_right=="true")
                                                {{number_format($value->amount,$currency->decimal_digit)."".$currency->symbole}}
                                            @else
                                                {{$currency->symbole."".number_format($value->amount,$currency->decimal_digit)}}
                                            @endif    
                                            </strong>
                                        </td>
                                        <td>{{$value->note ?: '-'}}</td>
                                        <td>
                                            <span class="date">{{ date('d F Y',strtotime($value->creer))}}</span><br>
                                            <small class="time text-muted">{{ date('h:i A',strtotime($value->creer))}}</small>
                                        </td>
                                        <td>
                                            @if($value->statut == 'success')
                                                <span class="badge badge-success p-2">Approved</span>
                                            @elseif($value->statut == 'rejected')
                                                <span class="badge badge-danger p-2">Rejected</span>
                                            @else
                                                <span class="badge badge-warning p-2">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($value->statut == 'pending')
                                                <button type="button" class="btn btn-sm btn-success btn-approve-payout" data-id="{{$value->id}}" title="Approve Request">
                                                    <i class="fa fa-check"></i> Approve
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger btn-reject-payout" data-id="{{$value->id}}" title="Reject Request">
                                                    <i class="fa fa-times"></i> Reject
                                                </button>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                @else
                                    <tr><td colspan="8" align="center">{{trans("lang.no_result")}}</td></tr>
                                @endif
                                </tbody>
                            </table>
                            <nav aria-label="Page navigation example" class="custom-pagination">
                                {{$withdrawal->appends(request()->query())->links()}}
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).on("click", ".btn-approve-payout", function (e) {
        if (!confirm("Are you sure you want to approve this payout request?")) return;
        var id = $(this).data('id');
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ url('withdrawal/accept') }}",
            data: {'id': id},
            method: "post",
            success: function (data) {
                window.location.reload();
            },
        });
    });

    $(document).on("click", ".btn-reject-payout", function (e) {
        if (!confirm("Are you sure you want to reject this payout request?")) return;
        var id = $(this).data('id');
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ url('withdrawal/reject') }}",
            data: {'id': id},
            method: "post",
            success: function (data) {
                window.location.reload();
            },
        });
    });
</script>
@endsection
