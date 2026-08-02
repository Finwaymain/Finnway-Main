@extends('layouts.app')

@section('content')

    <div class="page-wrapper ridedetail-page">
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">

                <h3 class="text-themecolor">{{trans('lang.document_details')}}</h3>

            </div>

            <div class="col-md-7 align-self-center">

                <ol class="breadcrumb">

                    <li class="breadcrumb-item">
                        <a href="{!! url('/dashboard') !!}">{{trans('lang.home')}}</a>
                    </li>

                    <li class="breadcrumb-item">
                        <a href="{!! url('drivers') !!}">{{trans('lang.driver_plural')}}</a>
                    </li>

                    <li class="breadcrumb-item active">
                        {{trans('lang.document_details')}}
                    </li>

                </ol>
                
                <div class="mt-3 text-right">
                    @if($driver->is_verified == 0 || $driver->statut == 'no')
                        <a href="{{ route('driver.verifyAndEnable', ['id' => $driver->id]) }}" class="btn btn-success text-white"><i class="fa fa-check mr-2"></i> Verify & Enable Driver</a>
                    @else
                        <span class="badge badge-success px-3 py-2" style="font-size: 14px;"><i class="fa fa-check mr-2"></i> Verified & Active</span>
                    @endif
                </div>

            </div>

        </div>

        <div class="container-fluid">

            <div class="row">

                <div class="col-12">

                    <div class="card">

                        <div class="card-body p-0 pb-5">

                            <div class="user-detail" role="tabpanel">

                                <div class="row">
                                    <div class="col-12 p-4">
                                        <div class="row mb-4">
                                            <!-- Bank Details -->
                                            <div class="col-md-4">
                                                <div class="box">
                                                    <div class="box-header bb-2 border-primary">
                                                        <h3 class="box-title">Bank Details</h3>
                                                    </div>
                                                    <div class="box-body">
                                                        <p><strong>Bank Name:</strong> {{ $driver->bank_name ?? 'N/A' }}</p>
                                                        <p><strong>Account No:</strong> {{ $driver->account_no ?? 'N/A' }}</p>
                                                        <p><strong>IFSC Code:</strong> {{ $driver->ifsc_code ?? 'N/A' }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Categories -->
                                            <div class="col-md-4">
                                                <div class="box">
                                                    <div class="box-header bb-2 border-primary">
                                                        <h3 class="box-title">Selected Services</h3>
                                                    </div>
                                                    <div class="box-body">
                                                        @if(isset($categories) && count($categories) > 0)
                                                            <ul class="pl-3">
                                                            @foreach($categories as $category)
                                                                <li>
                                                                    {{ $category->libelle }}
                                                                    <span class="badge badge-pill badge-{{ $category->statut == 'yes' ? 'success' : 'secondary' }}" style="font-size: 0.75rem; padding: 2px 8px; margin-left: 6px;">
                                                                        {{ $category->statut == 'yes' ? 'Active' : 'Inactive' }}
                                                                    </span>
                                                                </li>
                                                            @endforeach
                                                            </ul>
                                                        @else
                                                            <p>N/A</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Vehicles -->
                                            <div class="col-md-4">
                                                <div class="box">
                                                    <div class="box-header bb-2 border-primary">
                                                        <h3 class="box-title">Vehicles</h3>
                                                    </div>
                                                    <div class="box-body">
                                                        @if(isset($vehicles) && count($vehicles) > 0)
                                                            @foreach($vehicles as $key => $vehicle)
                                                                <div class="mb-3 p-2 bg-light rounded">
                                                                    <strong>{{ $vehicle->brand }} {{ $vehicle->model }}</strong><br>
                                                                    <small><strong>Type:</strong> {{ $vehicle->type_name ?? 'N/A' }}</small><br>
                                                                    <small><strong>Number Plate:</strong> {{ $vehicle->numberplate }}</small><br>
                                                                    <small><strong>Color:</strong> {{ $vehicle->color }} | <strong>Seats:</strong> {{ $vehicle->passenger }}</small><br>
                                                                    <small><strong>Mileage:</strong> {{ $vehicle->milage }} km/l | <strong>Driven:</strong> {{ $vehicle->km }} km</small>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <p>N/A</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                    </div>
                                    <div class="col-12">

                                        <div class="box">
                                            <div class="error_top"></div>
                                            @if($errors->any())
                                                <div class="alert alert-danger">
                                                    <ul>
                                                        @foreach($errors->all() as $error)
                                                            <li>{{ $error }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                            <div class="box-header bb-2 border-primary">
                                                <h3 class="box-title">{{ $driver->prenom }}'s {{trans('lang.documents')}}</h3>
                                            </div>
                                            <div class="box-body">
                                                <table class="table table-hover">
                                                    <thead>
	                                                    <tr>
	                                                        <th>{{trans('lang.s_no')}}</th>
	                                                        <th>{{trans('lang.document_name')}}</th>
	                                                        <th>{{trans('lang.status')}}</th>
	                                                        <th>{{trans('lang.comment')}}</th>
	                                                        <th>{{trans('lang.action')}}</th>
	                                                        <th>{{trans('lang.action')}}</th>
	                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @if(count($admin_documents) > 0)	
	                                                    @foreach($admin_documents as $key=>$document)
	                                                        <tr>
	                                                            <td><?php echo $key+1;?></td>
	                                                            <td>{{$document->title}}</td>
	                                                            <td>{{$document->driver_document?$document->driver_document->document_status:'Not Uploaded'}}</td>
	                                                            <td>{{$document->driver_document?$document->driver_document->comment:''}}</td>
	                                                            
	                                                            @if($document->driver_document)
	                                                            <td>
	    	                                                    	<a href="#" data-toggle="modal" data-target="#exampleModal_{{$document->id}}" class="open-image" title="View Document"><i class="imageresource fas fa fa-file-image-o"></i></a>
																	<a class="" href="{{ url('driver/uploaddocument',['id' => $document->driver_document?$document->driver_document->driver_id:$driver->id,'document_id'=>$document->id]) }}"><i class="fa fa-edit"></i></a>
																	
																	<div class="modal fade" id="exampleModal_{{$document->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
				                                                        <div class="modal-dialog" role="document" style="max-width: 50%;">
				                                                        	<div class="modal-content">
				                                                        
				                                                                <div class="modal-header">
				                                                                    <button type="button" class="close"
				                                                                            data-dismiss="modal"
				                                                                            aria-label="Close">
				                                                                        <span aria-hidden="true">&times;</span>
				                                                                    </button>
				                                                                </div>
				                                                        
				                                                                <div class="modal-body">
				                                                                    <div class="form-group">
			                                                                            <embed
			                                                                                src="{{ filter_var($document->driver_document->document_path, FILTER_VALIDATE_URL) ? $document->driver_document->document_path : asset('assets/images/driver/documents').'/'.$document->driver_document->document_path }}"
			                                                                                frameBorder="0"
			                                                                                scrolling="auto"
			                                                                                height="100%"
			                                                                                width="100%"
			                                                                                style="height: 540px;"
			                                                                            ></embed>
				                                                                    </div>
				                                                                    
				                                                                    <div class="modal-footer">
				                                                                        <a class="btn btn-primary" href="{{ filter_var($document->driver_document->document_path, FILTER_VALIDATE_URL) ? $document->driver_document->document_path : asset('assets/images/driver/documents').'/'.$document->driver_document->document_path }}" target="_blank">Download</a>
				                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{trans('lang.close')}}</button>
				                                                                    </div>
				                                                                </div>
				                                                            </div>
				                                                        </div>
				                                                    </div>
				                                                    
																</td>
																
																<td>
	                                                				<a href="{{ route('drivers.documentstatus',['id' => $document->driver_document->id,'type'=>1]) }}" class="btn btn-sm btn-success edit-form-btn">Approve</a>
	                                                				&nbsp;&nbsp;
	                                                				<a href="{{ route('drivers.documentstatus',['id' => $document->driver_document->id,'type'=>0]) }}" class="btn btn-sm btn-danger edit-form-btn" data-toggle="modal" data-target="#commentModal" data-docid="{{$document->driver_document->id}}">Disapprove</a>
	                                                			</td>
	                                                			
	                                                           @else
	                                                                <td><a href="{{ url('driver/uploaddocument',['id' => $document->driver_document?$document->driver_document->driver_id:$driver->id,'document_id'=>$document->id]) }}" class="fas fa fa-upload"></a></td>
	                                                                <td></td>
	                                                            @endif
	                                                        
	                                                        </tr>
	                                                    	
														@endforeach
													 @else
													 	<tr><td colspan="6" align="center">{{trans('lang.no_result')}}</td></tr>
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
                </div>
            </div>
        </div>
        
        <div class="modal fade" id="commentModal" tabindex="-1" role="dialog" aria-labelledby="commentModalLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="commentModalLabel">{{trans('lang.add_comment_disapprove')}}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form>
					<div class="form-group">
						<label for="message-text" class="col-form-label">{{trans('lang.comment')}}:</label>
						<textarea class="form-control" id="comment"></textarea>
					</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary send-msg">{{trans('lang.save')}}</button>
					<button type="button" class="btn btn-secondary" data-dismiss="modal">{{trans('lang.close')}}</button>
				</div>
				</div>
			</div>
		</div>

    @endsection

    @section('scripts')
    
    <script type="text/javascript">
    	$('#commentModal').on('show.bs.modal', function (event) {
		  var button = $(event.relatedTarget);
		  var docid = button.data('docid');
		  var modal = $(this);
		  modal.attr('data-docid',docid);
		});
		
		$('#commentModal').on('hide.bs.modal', function(){
    	  $(this).removeAttr('data-docid');
  		});
  		
  		$('.send-msg').click(function(){
  			
  			var docid = $('#commentModal').attr('data-docid');
  			var comment = $('#commentModal').find('#comment').val();
  			
  			if(comment == ''){
  				alert("{{trans('lang.add_comment_disapprove')}}");
  				return false;
  			}
  			var url = "{{ route('drivers.documentstatus',['id','type'=>0]) }}";
  			url = url.replace('id',docid);
  			$.ajax({
                url: url,
                type: "GET",
                data: {
                    docid:docid,
                    comment: comment
                },
                dataType: 'json',
                success: function (data) {
                	window.location.reload();
                }
            });
  		});
    </script>
 
@endsection
