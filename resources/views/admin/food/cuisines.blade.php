@extends('admin.food.layout')

@section('food')
<div class="container-fluid">

  {{-- Page Header --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-0 font-weight-bold"><i class="fas fa-utensils text-warning mr-2"></i>Cuisine Types</h4>
      <small class="text-muted">Master list shown to restaurant partners as multi-select dropdown during registration.</small>
    </div>
    <button class="btn btn-warning" data-toggle="modal" data-target="#addCuisineModal">
      <i class="fas fa-plus mr-1"></i> Add Cuisine
    </button>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
      <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
      <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
  @endif

  {{-- Cuisine Cards Grid --}}
  <div class="card shadow-sm">
    <div class="card-body p-0">
      <table class="table table-hover mb-0">
        <thead class="thead-light">
          <tr>
            <th width="50">#</th>
            <th width="60">Icon</th>
            <th>Cuisine Name</th>
            <th>Slug</th>
            <th>Sort Order</th>
            <th width="100">Status</th>
            <th width="140">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($cuisines as $cuisine)
          <tr>
            <td>{{ $cuisine->id }}</td>
            <td class="text-center" style="font-size:22px;">{{ $cuisine->icon ?: '🍽️' }}</td>
            <td class="font-weight-bold">{{ $cuisine->name }}</td>
            <td><code>{{ $cuisine->slug }}</code></td>
            <td>{{ $cuisine->sort_order }}</td>
            <td>
              @if($cuisine->is_active)
                <span class="badge badge-success">Active</span>
              @else
                <span class="badge badge-secondary">Inactive</span>
              @endif
            </td>
            <td>
              <button class="btn btn-sm btn-outline-primary edit-btn"
                data-id="{{ $cuisine->id }}"
                data-name="{{ $cuisine->name }}"
                data-icon="{{ $cuisine->icon }}"
                data-sort="{{ $cuisine->sort_order }}"
                data-active="{{ $cuisine->is_active ? '1' : '0' }}"
                data-toggle="modal" data-target="#editCuisineModal">
                <i class="fas fa-edit"></i>
              </button>
              <form method="POST" action="{{ route('admin.food.cuisines.delete', $cuisine->id) }}"
                    class="d-inline" onsubmit="return confirm('Delete this cuisine?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          @empty
          <tr><td colspan="7" class="text-center text-muted py-4">No cuisines yet. Click "Add Cuisine" to create your first one.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Info card --}}
  <div class="alert alert-info mt-3">
    <i class="fas fa-info-circle mr-2"></i>
    <strong>How it works:</strong> Restaurant partners see this list as a multi-select chip picker during onboarding.
    They can select one or more cuisines (e.g. North Indian + Chinese). Selected cuisines are stored per restaurant and shown to customers on the Fiinway app.
  </div>

</div>

{{-- Add Cuisine Modal --}}
<div class="modal fade" id="addCuisineModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <form method="POST" action="{{ route('admin.food.cuisines.save') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-plus-circle mr-2 text-warning"></i>Add Cuisine</h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="font-weight-bold">Cuisine Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Chettinad" required>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Icon (emoji)</label>
            <input type="text" name="icon" class="form-control" placeholder="e.g. 🍛" maxlength="10">
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Sort Order</label>
            <input type="number" name="sort_order" class="form-control" value="{{ $cuisines->count() + 1 }}" min="0">
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="addActive" checked>
            <label class="form-check-label" for="addActive">Active (visible to partners)</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-save mr-1"></i>Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Edit Cuisine Modal --}}
<div class="modal fade" id="editCuisineModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <form method="POST" id="editCuisineForm">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-edit mr-2 text-primary"></i>Edit Cuisine</h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="font-weight-bold">Cuisine Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="editName" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Icon (emoji)</label>
            <input type="text" name="icon" id="editIcon" class="form-control" maxlength="10">
          </div>
          <div class="form-group">
            <label class="font-weight-bold">Sort Order</label>
            <input type="number" name="sort_order" id="editSort" class="form-control" min="0">
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editActive">
            <label class="form-check-label" for="editActive">Active</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save mr-1"></i>Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.edit-btn').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var id = this.dataset.id;
    document.getElementById('editName').value = this.dataset.name;
    document.getElementById('editIcon').value = this.dataset.icon;
    document.getElementById('editSort').value = this.dataset.sort;
    document.getElementById('editActive').checked = this.dataset.active === '1';
    document.getElementById('editCuisineForm').action = '/admin/food/cuisines/' + id;
  });
});
</script>
@endpush
@endsection
