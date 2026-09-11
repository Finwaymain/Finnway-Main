@extends("admin.food.layout")

@section("food")
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">⚙️ Food Module Global Settings</h3>
            <p class="text-muted mb-0">Configure operational rules, auto-accept toggles, surge multipliers, and default SLA timings.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="post" action="{{ route('admin.food.settings.save') }}">
                @csrf
                <div class="row">
                    @forelse($settings as $key => $val)
                        <div class="col-md-6 form-group mb-3">
                            <label class="font-weight-bold small text-muted text-uppercase mb-1">
                                {{ ucwords(str_replace('_', ' ', $key)) }}
                            </label>
                            <input type="text" name="{{ $key }}" value="{{ $val }}" class="form-control">
                            <small class="text-muted">Key: <code>{{ $key }}</code></small>
                        </div>
                    @empty
                        <div class="col-12 text-center py-4 text-muted">
                            No food settings keys found. Default system values active.
                        </div>
                    @endforelse
                </div>

                <div class="mt-4 pt-3 border-top text-right">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa fa-save mr-1"></i> Save Food Module Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
