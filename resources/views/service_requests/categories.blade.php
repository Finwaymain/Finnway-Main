@extends('layouts.app')

@section('content')
<style>
.hs-header-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px 24px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.hs-header-card h3 {
    color: #0f172a;
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0;
}
.hs-header-card p {
    color: #64748b;
    font-size: 0.875rem;
    margin: 4px 0 0 0;
}

.hs-section-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    overflow: hidden;
}
.hs-section-card .hs-card-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.hs-section-card .hs-card-header h5 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
}
.hs-section-card .hs-card-body {
    padding: 20px;
}

.form-label-custom {
    font-size: 0.8rem;
    font-weight: 600;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin-bottom: 6px;
    display: block;
}
.form-control-custom {
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    font-size: 0.9rem;
    color: #1e293b;
    padding: 9px 13px;
    height: 42px;
    width: 100%;
    box-sizing: border-box;
    transition: all 0.15s ease;
}
.form-control-custom:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
    outline: none;
}

/* Radio Option Box */
.radio-pill-container {
    display: flex;
    align-items: center;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 6px 12px;
    gap: 16px;
    height: 42px;
}
.radio-pill-container label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #334155;
    margin: 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
}
.radio-pill-container input[type="radio"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: #2563eb;
}

/* Switch Element */
.switch-label {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    margin: 0;
    cursor: pointer;
    vertical-align: middle;
}
.switch-label input { opacity: 0; width: 0; height: 0; }
.switch-slider {
    position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
    background-color: #cbd5e1; transition: .2s ease; border-radius: 24px;
}
.switch-slider:before {
    position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px;
    background-color: white; transition: .2s ease; border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}
.switch-label input:checked + .switch-slider { background-color: #2563eb; }
.switch-label input:checked + .switch-slider:before { transform: translateX(20px); }

/* Buttons */
.btn-blue-primary {
    background: #2563eb;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    padding: 9px 20px;
    font-weight: 600;
    font-size: 0.875rem;
    height: 42px;
    transition: all 0.15s ease;
    cursor: pointer;
}
.btn-blue-primary:hover {
    background: #1d4ed8;
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(37,99,235,0.25);
}

/* Table styling */
.hs-table {
    width: 100%;
    border-collapse: collapse;
}
.hs-table th {
    background: #f8fafc;
    color: #475569;
    font-weight: 700;
    font-size: 0.8rem;
    padding: 12px 16px;
    border-bottom: 2px solid #e2e8f0;
    text-transform: uppercase;
}
.hs-table td {
    padding: 14px 16px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.875rem;
    color: #1e293b;
    vertical-align: middle;
}

/* Sub skills list items */
.skill-item-pill {
    padding: 12px 16px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #ffffff;
    font-weight: 600;
    font-size: 0.875rem;
    color: #334155;
    margin-bottom: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
}
.skill-item-pill:hover, .skill-item-pill.active {
    background: #eff6ff;
    border-color: #2563eb;
    color: #1d4ed8;
    box-shadow: 0 2px 8px rgba(37,99,235,0.08);
}

.sub-skill-card {
    padding: 12px 16px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #ffffff;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
}

/* Info note box */
.info-note-box {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 12px;
    padding: 16px 20px;
    margin-top: 20px;
}
.info-note-box h6 {
    color: #1d4ed8;
    font-size: 0.9rem;
    font-weight: 700;
    margin-bottom: 8px;
}
.info-note-box ul {
    margin: 0;
    padding-left: 20px;
    color: #1e40af;
    font-size: 0.85rem;
}
.info-note-box li {
    margin-bottom: 4px;
}

/* Custom Toast Container */
#hsToastContainer {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    pointer-events: none;
}
.hs-toast {
    min-width: 260px;
    padding: 12px 18px;
    border-radius: 8px;
    background: #0f172a;
    color: #ffffff;
    font-size: 0.875rem;
    font-weight: 600;
    box-shadow: 0 4px 14px rgba(0,0,0,0.15);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    pointer-events: auto;
    animation: fadeInRight 0.3s ease-in-out;
}
.hs-toast.success { background: #166534; }
.hs-toast.error { background: #991b1b; }
</style>

<div class="page-wrapper" style="padding: 20px;">
    <div id="hsToastContainer"></div>

    <!-- Top Header Card -->
    <div class="hs-header-card d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                <i class="mdi mdi-home-outline"></i>
            </div>
            <div>
                <h3>Home Service Management</h3>
                <p>Add, organize and manage home services, skills and sub services with commission settings.</p>
            </div>
        </div>
        <a href="{{ url('/dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left mr-1"></i> Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" style="border-radius:10px;" role="alert">
            <i class="fa fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <!-- Card 1: Inline Form for Parent Service (Level 1) -->
    <div class="hs-section-card">
        <div class="hs-card-header">
            <h5 id="parentFormTitle"><i class="fa fa-plus-circle text-primary mr-2"></i>Add Parent Service</h5>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAddParentForm()">
                <i class="fa fa-eye mr-1"></i> Toggle Form
            </button>
        </div>
        <div class="hs-card-body" id="addParentForm" style="display: block;">
            <form id="parentServiceForm" onsubmit="submitParentForm(event)">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $root->id }}">
                <input type="hidden" id="parent_edit_id" value="">
                <input type="hidden" name="commission_type" value="percentage">
                <div class="row align-items-end">
                    <div class="col-md-6 mb-3">
                        <label class="form-label-custom">Parent Service Name <span class="text-danger">*</span></label>
                        <input type="text" name="libelle" id="parent_libelle_input" class="form-control form-control-custom" placeholder="e.g. Home Cleaning, Plumbing, Electrical" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label-custom">Commission (%)</label>
                        <input type="number" name="commission_value" id="parent_comm_val_input" class="form-control form-control-custom" placeholder="10" step="0.1" min="0" value="10" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <button type="submit" id="parentSubmitBtn" class="btn btn-blue-primary w-100">
                            <i class="fa fa-save mr-1"></i> <span id="parentBtnText">Save Parent Service</span>
                        </button>
                        <button type="button" id="parentCancelBtn" class="btn btn-sm btn-light w-100 mt-2" style="display:none;" onclick="resetParentForm()">
                            Cancel Edit
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Card 2: Parent Service List (Live DB Data) -->
    <div class="hs-section-card">
        <div class="hs-card-header">
            <h5>Parent Service List ( <span id="parentServiceCount">{{ count($parentServices) }}</span> )</h5>
            <div style="width: 260px;">
                <input type="text" id="parentSearchInput" onkeyup="filterParentTable()" class="form-control form-control-custom py-1 px-3" placeholder="Search parent services...">
            </div>
        </div>
        <div class="hs-card-body p-0">
            <div class="table-responsive">
                <table class="hs-table" id="parentServiceTable">
                    <thead>
                        <tr>
                            <th style="width:70px;">S.No</th>
                            <th>Parent Service</th>
                            <th>Commission (%)</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parentServices as $idx => $pService)
                        @php
                            $icons = [
                                'Home Cleaning' => 'mdi-home-search-outline',
                                'Plumbing' => 'mdi-wrench-outline',
                                'AC & Geyser Service' => 'mdi-television-guide',
                                'Painting' => 'mdi-format-paint',
                                'Electrical' => 'mdi-flash-outline'
                            ];
                            $iconClass = $icons[$pService->libelle] ?? 'mdi-cog-outline';
                        @endphp
                        <tr id="parent_row_{{ $pService->id }}">
                            <td><strong>{{ $idx + 1 }}</strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div style="width:32px; height:32px; border-radius:8px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; font-size:1.15rem; margin-right:12px;">
                                        <i class="mdi {{ $iconClass }}"></i>
                                    </div>
                                    <span class="font-weight-bold text-dark" id="parent_title_{{ $pService->id }}">{{ $pService->libelle }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-blue font-weight-bold text-primary px-2.5 py-1.5" id="parent_badge_{{ $pService->id }}" style="background:#eff6ff; border-radius:6px; font-size:0.85rem;">
                                    {{ $pService->commission_value ?? 10 }}%
                                </span>
                            </td>
                            <td>
                                <label class="switch-label">
                                    <input type="checkbox" id="status_chk_{{ $pService->id }}" {{ $pService->statut ? 'checked' : '' }} onchange="toggleParentStatus('{{ $pService->id }}', this)">
                                    <span class="switch-slider"></span>
                                </label>
                            </td>
                            <td style="text-align:right;">
                                <button type="button" class="btn btn-sm btn-light text-primary mr-1" onclick="startEditParent('{{ $pService->id }}', '{{ addslashes($pService->libelle) }}', '{{ $pService->commission_value ?? 10 }}')" title="Edit Parent Service">
                                    <i class="fa fa-pencil-alt"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light text-danger" onclick="deleteParentService('{{ $pService->id }}', '{{ addslashes($pService->libelle) }}')" title="Delete Parent Service">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No parent services found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Card 3: Inline Skill Category (Level 2) & Sub Skills (Level 3) -->
    <div class="hs-section-card">
        <div class="hs-card-header">
            <div class="d-flex align-items-center gap-2">
                <div style="width:32px; height:32px; border-radius:8px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; font-size:1.15rem;">
                    <i class="mdi mdi-cog-outline"></i>
                </div>
                <h5 class="mb-0">Skill Categories & Sub-Skills</h5>
            </div>
        </div>
        <div class="hs-card-body">
            <div class="row">
                <!-- Left Column: Skill Category Inline Add/Edit & List (Level 2) -->
                <div class="col-md-5 mb-4">
                    <label class="form-label-custom">Select Parent Service</label>
                    <select id="parentServiceSelect" class="form-control form-control-custom mb-3" onchange="loadSkillsForParent(this.value)">
                        @foreach($parentServices as $pService)
                        <option value="{{ $pService->id }}">{{ $pService->libelle }}</option>
                        @endforeach
                    </select>

                    <div class="p-3 mb-3 bg-light rounded" style="border:1px solid #e2e8f0;">
                        <span class="font-weight-bold text-dark d-block mb-2" id="skillFormTitle">Add New Skill Category</span>
                        <form id="skillCategoryForm" onsubmit="submitSkillCategoryForm(event)">
                            @csrf
                            <input type="hidden" id="skill_edit_id" value="">
                            <div class="form-group mb-2">
                                <input type="text" id="skillCategoryInput" class="form-control form-control-custom" placeholder="e.g. Deep Cleaning, Wiring Repair" required>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" id="skillSubmitBtn" class="btn btn-blue-primary btn-sm flex-grow-1" style="height:36px;">
                                    <i class="fa fa-plus mr-1"></i> <span id="skillBtnText">Add Skill Category</span>
                                </button>
                                <button type="button" id="skillCancelBtn" class="btn btn-secondary btn-sm" style="display:none; height:36px;" onclick="resetSkillCategoryForm()">Cancel</button>
                            </div>
                        </form>
                    </div>

                    <label class="form-label-custom mb-2">Skill Categories</label>
                    <div id="skillsCategoryList">
                        <div class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin mr-1"></i> Loading skills...</div>
                    </div>
                </div>

                <!-- Right Column: Sub Skill Inline Add/Edit & List (Level 3) -->
                <div class="col-md-7 mb-4">
                    <div class="p-3 mb-3 bg-light rounded" style="border:1px solid #e2e8f0;">
                        <span class="font-weight-bold text-dark d-block mb-2" id="subSkillFormTitle">Add Sub Skill (for <span id="selectedSkillName" class="text-primary">Selected Category</span>)</span>
                        <form id="subSkillForm" onsubmit="submitSubSkillForm(event)">
                            @csrf
                            <input type="hidden" id="selectedSkillId" value="0">
                            <input type="hidden" id="sub_skill_edit_id" value="">
                            <div class="input-group" style="display: flex;">
                                <input type="text" id="subSkillInput" class="form-control form-control-custom" style="border-top-right-radius:0; border-bottom-right-radius:0; height:38px;" placeholder="Enter Sub Skill Name (e.g. Floor Cleaning)" required>
                                <button type="submit" id="subSkillSubmitBtn" class="btn btn-blue-primary" style="border-top-left-radius:0; border-bottom-left-radius:0; white-space: nowrap; height:38px;">
                                    <i class="fa fa-plus mr-1"></i> <span id="subSkillBtnText">Add Sub Skill</span>
                                </button>
                                <button type="button" id="subSkillCancelBtn" class="btn btn-secondary" style="display:none; border-top-left-radius:0; border-bottom-left-radius:0; height:38px;" onclick="resetSubSkillForm()">Cancel</button>
                            </div>
                        </form>
                    </div>

                    <label class="form-label-custom mb-2">Sub Skills List</label>
                    <div id="subSkillsList">
                        <div class="text-center text-muted py-3">Select a skill category on the left to view sub-skills.</div>
                    </div>
                </div>
            </div>

            <!-- Bottom Info Note Box -->
            <div class="info-note-box">
                <div class="d-flex align-items-center gap-2">
                    <i class="mdi mdi-information-outline text-primary" style="font-size:1.2rem;"></i>
                    <h6 class="mb-0">Note:</h6>
                </div>
                <ul class="mt-2 mb-0">
                    <li>Commission applies to all Skills & Sub Skills (inherits from Parent Service).</li>
                    <li>Business User sees the commission during registration.</li>
                    <li>Admin changes only Parent Service commission.</li>
                </ul>
            </div>

        </div>
    </div>

</div>

<script>
let currentParentId = null;
let currentSkillId = null;

document.addEventListener('DOMContentLoaded', function() {
    const parentSelect = document.getElementById('parentServiceSelect');
    if (parentSelect && parentSelect.value) {
        currentParentId = parentSelect.value;
        loadSkillsForParent(parentSelect.value);
    }
});

function showHsToast(message, type = 'success') {
    const container = document.getElementById('hsToastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `hs-toast ${type}`;
    toast.innerHTML = `<i class="fa ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${escapeHtml(message)}`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function toggleAddParentForm() {
    const el = document.getElementById('addParentForm');
    if (el) {
        el.style.display = (el.style.display === 'none') ? 'block' : 'none';
    }
}

function filterParentTable() {
    const input = document.getElementById('parentSearchInput');
    const term = input.value.toLowerCase();
    const rows = document.querySelectorAll('#parentServiceTable tbody tr');
    rows.forEach(function(row) {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
    });
}

/* ------------------- LEVEL 1: PARENT SERVICE INLINE EDIT / STORE / TOGGLE / DELETE ------------------- */

function startEditParent(id, name, commVal) {
    document.getElementById('parent_edit_id').value = id;
    document.getElementById('parent_libelle_input').value = name;
    document.getElementById('parent_comm_val_input').value = commVal;

    document.getElementById('parentFormTitle').innerHTML = '<i class="fa fa-edit text-primary mr-2"></i>Edit Parent Service: ' + escapeHtml(name);
    document.getElementById('parentBtnText').innerText = 'Update Parent Service';
    document.getElementById('parentCancelBtn').style.display = 'block';

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetParentForm() {
    document.getElementById('parentServiceForm').reset();
    document.getElementById('parent_edit_id').value = '';
    document.getElementById('parentFormTitle').innerHTML = '<i class="fa fa-plus-circle text-primary mr-2"></i>Add Parent Service';
    document.getElementById('parentBtnText').innerText = 'Save Parent Service';
    document.getElementById('parentCancelBtn').style.display = 'none';
}

function submitParentForm(e) {
    const editId = document.getElementById('parent_edit_id').value;
    if (!editId) {
        // Standard form submit for new parent service
        document.getElementById('parentServiceForm').action = "{{ route('home_services.store') }}";
        document.getElementById('parentServiceForm').method = "POST";
        return true;
    }

    e.preventDefault();
    const form = document.getElementById('parentServiceForm');
    const formData = new FormData(form);

    fetch("/home-services/update/" + editId, {
        method: "POST",
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            showHsToast('Parent Service updated successfully.', 'success');
            const titleSpan = document.getElementById('parent_title_' + editId);
            if (titleSpan) titleSpan.innerText = res.libelle;
            const badgeSpan = document.getElementById('parent_badge_' + editId);
            if (badgeSpan) {
                badgeSpan.innerText = (formData.get('commission_value') || 10) + '%';
            }
            resetParentForm();
        } else {
            showHsToast('Failed to update Parent Service.', 'error');
        }
    })
    .catch(err => showHsToast('Error updating Parent Service.', 'error'));
}

function toggleParentStatus(id, checkbox) {
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');

    fetch("/home-services/toggle/" + id, {
        method: "POST",
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            checkbox.checked = res.statut == 1;
            showHsToast(res.message || 'Status updated successfully.', 'success');
        } else {
            checkbox.checked = !checkbox.checked;
            showHsToast('Failed to update status.', 'error');
        }
    })
    .catch(err => {
        checkbox.checked = !checkbox.checked;
        showHsToast('Server error while toggling status.', 'error');
    });
}

function deleteParentService(id, name) {
    if (confirm(`Delete Parent Service "${name}" and all its sub-skills?`)) {
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('_method', 'DELETE');

        fetch("/home-services/" + id, {
            method: "POST",
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                showHsToast('Parent Service deleted successfully.', 'success');
                const row = document.getElementById('parent_row_' + id);
                if (row) row.remove();
            } else {
                showHsToast('Failed to delete Parent Service.', 'error');
            }
        })
        .catch(err => showHsToast('Error deleting Parent Service.', 'error'));
    }
}

/* ------------------- LEVEL 2: SKILL CATEGORY INLINE EDIT / STORE / DELETE ------------------- */

function startEditSkill(id, name) {
    document.getElementById('skill_edit_id').value = id;
    document.getElementById('skillCategoryInput').value = name;
    document.getElementById('skillFormTitle').innerText = 'Edit Skill Category: ' + name;
    document.getElementById('skillBtnText').innerText = 'Update Skill';
    document.getElementById('skillCancelBtn').style.display = 'inline-block';
}

function resetSkillCategoryForm() {
    document.getElementById('skill_edit_id').value = '';
    document.getElementById('skillCategoryInput').value = '';
    document.getElementById('skillFormTitle').innerText = 'Add New Skill Category';
    document.getElementById('skillBtnText').innerText = 'Add Skill Category';
    document.getElementById('skillCancelBtn').style.display = 'none';
}

function submitSkillCategoryForm(e) {
    e.preventDefault();
    const editId = document.getElementById('skill_edit_id').value;
    const name = document.getElementById('skillCategoryInput').value.trim();
    const parentId = document.getElementById('parentServiceSelect').value;

    if (!editId) {
        // Store new skill
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('parent_id', parentId);
        formData.append('libelle', name);

        fetch("/home-services/skill", {
            method: "POST",
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                resetSkillCategoryForm();
                showHsToast('Skill Category added successfully.', 'success');
                loadSkillsForParent(parentId, res.id);
            } else {
                showHsToast('Failed to add Skill Category.', 'error');
            }
        })
        .catch(err => showHsToast('Error adding Skill Category.', 'error'));
    } else {
        // Update existing skill
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('libelle', name);

        fetch("/home-services/update/" + editId, {
            method: "POST",
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                resetSkillCategoryForm();
                showHsToast('Skill Category updated successfully.', 'success');
                if (currentParentId) loadSkillsForParent(currentParentId, editId);
            } else {
                showHsToast('Failed to update Skill Category.', 'error');
            }
        })
        .catch(err => showHsToast('Error updating Skill Category.', 'error'));
    }
}

function loadSkillsForParent(parentId, targetSkillId = null) {
    if (!parentId || parentId == "0" || parentId == "undefined") {
        document.getElementById('skillsCategoryList').innerHTML = '<div class="text-center text-muted py-3 small">Please select a parent service.</div>';
        return;
    }
    currentParentId = parentId;
    resetSkillCategoryForm();
    resetSubSkillForm();

    const listDiv = document.getElementById('skillsCategoryList');
    const subDiv = document.getElementById('subSkillsList');
    listDiv.innerHTML = '<div class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin mr-1"></i> Loading skills...</div>';
    subDiv.innerHTML = '<div class="text-center text-muted py-3">Select a skill category on the left to view sub-skills.</div>';

    fetch("/home-services/skills/" + parentId, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(async res => {
            const data = await res.json().catch(() => null);
            if (!res.ok || !data) {
                throw new Error((data && data.message) ? data.message : 'HTTP Error ' + res.status);
            }
            return data;
        })
        .then(res => {
            if (res.success && res.skills && res.skills.length > 0) {
                let html = '';
                let activeSkill = res.skills[0];

                if (targetSkillId) {
                    const found = res.skills.find(s => s.id == targetSkillId);
                    if (found) activeSkill = found;
                }

                res.skills.forEach(function(skill) {
                    const activeClass = (skill.id == activeSkill.id) ? 'active' : '';
                    html += `
                    <div class="skill-item-pill ${activeClass}" id="skill_pill_${skill.id}" onclick="selectSkillPill(this, '${skill.id}', '${escapeJs(skill.libelle)}')">
                        <span class="font-weight-bold" id="skill_name_${skill.id}">${escapeHtml(skill.libelle)}</span>
                        <div class="d-flex align-items-center">
                            <span class="badge badge-primary badge-pill mr-2">${skill.sub_skills_count}</span>
                            <button type="button" class="btn btn-xs btn-link text-primary p-0 mr-2" onclick="event.stopPropagation(); startEditSkill('${skill.id}', '${escapeJs(skill.libelle)}')" title="Edit Skill Category"><i class="fa fa-pencil-alt"></i></button>
                            <button type="button" class="btn btn-xs btn-link text-danger p-0" onclick="event.stopPropagation(); deleteCategoryItem('${skill.id}', 'skill', '${escapeJs(skill.libelle)}')" title="Delete Skill Category"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>`;
                });
                listDiv.innerHTML = html;

                selectSkillPill(document.getElementById('skill_pill_' + activeSkill.id), activeSkill.id, activeSkill.libelle);
            } else {
                listDiv.innerHTML = '<div class="text-center text-muted py-3 small">No skill categories found. Use form above to add one.</div>';
                const nameEl = document.getElementById('selectedSkillName');
                if (nameEl) nameEl.innerText = 'None';
                const idEl = document.getElementById('selectedSkillId');
                if (idEl) idEl.value = 0;
                currentSkillId = null;
            }
        })
        .catch(err => {
            console.error("loadSkillsForParent error:", err);
            listDiv.innerHTML = '<div class="text-center text-danger py-3 small">Failed to load skills: ' + escapeHtml(err.message || 'Error') + '</div>';
        });
}

function selectSkillPill(element, skillId, skillName) {
    currentSkillId = skillId;
    const pills = document.querySelectorAll('.skill-item-pill');
    pills.forEach(p => p.classList.remove('active'));
    if (element) element.classList.add('active');

    document.getElementById('selectedSkillName').innerText = skillName;
    document.getElementById('selectedSkillId').value = skillId;

    loadSubSkillsForSkill(skillId, skillName);
}

/* ------------------- LEVEL 3: SUB SKILL INLINE EDIT / STORE / DELETE ------------------- */

function startEditSubSkill(id, name) {
    const editIdEl = document.getElementById('sub_skill_edit_id');
    if (editIdEl) editIdEl.value = id;
    const inputEl = document.getElementById('subSkillInput');
    if (inputEl) inputEl.value = name;
    const titleEl = document.getElementById('subSkillFormTitle');
    if (titleEl) {
        titleEl.innerHTML = 'Edit Sub Skill: <span id="selectedSkillName" class="text-primary">' + escapeHtml(name) + '</span>';
    }
    const btnTextEl = document.getElementById('subSkillBtnText');
    if (btnTextEl) btnTextEl.innerText = 'Update Sub Skill';
    const cancelBtn = document.getElementById('subSkillCancelBtn');
    if (cancelBtn) cancelBtn.style.display = 'inline-block';
}

function resetSubSkillForm() {
    const editIdEl = document.getElementById('sub_skill_edit_id');
    if (editIdEl) editIdEl.value = '';
    const inputEl = document.getElementById('subSkillInput');
    if (inputEl) inputEl.value = '';
    const nameEl = document.getElementById('selectedSkillName');
    const skillName = nameEl ? nameEl.innerText : 'Selected Category';
    const titleEl = document.getElementById('subSkillFormTitle');
    if (titleEl) {
        titleEl.innerHTML = 'Add Sub Skill (for <span id="selectedSkillName" class="text-primary">' + escapeHtml(skillName) + '</span>)';
    }
    const btnTextEl = document.getElementById('subSkillBtnText');
    if (btnTextEl) btnTextEl.innerText = 'Add Sub Skill';
    const cancelBtn = document.getElementById('subSkillCancelBtn');
    if (cancelBtn) cancelBtn.style.display = 'none';
}

function submitSubSkillForm(e) {
    e.preventDefault();
    const editId = document.getElementById('sub_skill_edit_id').value;
    const skillId = document.getElementById('selectedSkillId').value;
    const name = document.getElementById('subSkillInput').value.trim();

    if (!editId) {
        if (!skillId || skillId === "0") {
            showHsToast("Please select a Skill Category on the left first.", "error");
            return;
        }

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('parent_id', skillId);
        formData.append('libelle', name);

        fetch("/home-services/sub-skill", {
            method: "POST",
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(async res => {
            const data = await res.json().catch(() => null);
            if (!res.ok || !data) throw new Error((data && data.message) ? data.message : 'HTTP Error ' + res.status);
            return data;
        })
        .then(res => {
            if (res.success) {
                resetSubSkillForm();
                showHsToast('Sub skill added successfully.', 'success');
                loadSubSkillsForSkill(skillId, document.getElementById('selectedSkillName').innerText);
                if (currentParentId) loadSkillsForParent(currentParentId, skillId);
            } else {
                showHsToast(res.message || 'Failed to add sub skill.', 'error');
            }
        })
        .catch(err => showHsToast('Error: ' + err.message, 'error'));
    } else {
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('libelle', name);

        fetch("/home-services/update/" + editId, {
            method: "POST",
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(async res => {
            const data = await res.json().catch(() => null);
            if (!res.ok || !data) throw new Error((data && data.message) ? data.message : 'HTTP Error ' + res.status);
            return data;
        })
        .then(res => {
            if (res.success) {
                resetSubSkillForm();
                showHsToast('Sub Skill updated successfully.', 'success');
                if (currentSkillId) loadSubSkillsForSkill(currentSkillId, document.getElementById('selectedSkillName').innerText);
            } else {
                showHsToast(res.message || 'Failed to update Sub Skill.', 'error');
            }
        })
        .catch(err => showHsToast('Error: ' + err.message, 'error'));
    }
}

function loadSubSkillsForSkill(skillId, skillName) {
    if (!skillId || skillId == "0" || skillId == "undefined") {
        document.getElementById('subSkillsList').innerHTML = '<div class="text-center text-muted py-3 small">Select a skill category on the left to view sub-skills.</div>';
        return;
    }
    currentSkillId = skillId;
    resetSubSkillForm();

    const subDiv = document.getElementById('subSkillsList');
    subDiv.innerHTML = '<div class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin mr-1"></i> Loading sub-skills...</div>';

    fetch("/home-services/sub-skills/" + skillId, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(async res => {
            const data = await res.json().catch(() => null);
            if (!res.ok || !data) throw new Error((data && data.message) ? data.message : 'HTTP Error ' + res.status);
            return data;
        })
        .then(res => {
            if (res.success && res.sub_skills && res.sub_skills.length > 0) {
                let html = '';
                res.sub_skills.forEach(function(sub) {
                    html += `
                    <div class="sub-skill-card" id="sub_skill_card_${sub.id}">
                        <span class="d-flex align-items-center font-weight-bold text-dark">
                            <i class="fa fa-check-circle text-primary mr-2"></i> <span id="sub_title_${sub.id}">${escapeHtml(sub.libelle)}</span>
                        </span>
                        <div>
                            <button type="button" class="btn btn-sm text-primary p-0 mr-3" style="background:none; border:none; cursor:pointer;" onclick="startEditSubSkill('${sub.id}', '${escapeJs(sub.libelle)}')" title="Edit Sub-Skill"><i class="fa fa-pencil-alt"></i></button>
                            <button type="button" class="btn btn-sm text-danger p-0" style="background:none; border:none; cursor:pointer;" onclick="deleteCategoryItem('${sub.id}', 'subskill', '${escapeJs(sub.libelle)}')" title="Delete Sub-Skill"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>`;
                });
                subDiv.innerHTML = html;
            } else {
                subDiv.innerHTML = '<div class="text-center text-muted py-3 small">No sub-skills added for ' + escapeHtml(skillName) + ' yet. Use the form above to add sub-skills.</div>';
            }
        })
        .catch(err => {
            console.error("loadSubSkillsForSkill error:", err);
            subDiv.innerHTML = '<div class="text-center text-danger py-3 small">Failed to load sub-skills: ' + escapeHtml(err.message || 'Error') + '</div>';
        });
}

function deleteCategoryItem(id, type, name) {
    const label = type === 'skill' ? 'Skill Category' : 'Sub-Skill';
    if (confirm(`Delete ${label} "${name}"?`)) {
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('_method', 'DELETE');

        fetch("/home-services/" + id, {
            method: "POST",
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                showHsToast(`${label} deleted successfully.`, 'success');
                if (type === 'skill') {
                    if (currentParentId) loadSkillsForParent(currentParentId);
                } else if (type === 'subskill') {
                    const card = document.getElementById('sub_skill_card_' + id);
                    if (card) card.remove();
                    if (currentParentId && currentSkillId) loadSkillsForParent(currentParentId, currentSkillId);
                }
            } else {
                showHsToast(`Failed to delete ${label}.`, 'error');
            }
        })
        .catch(err => showHsToast(`Error deleting ${label}.`, 'error'));
    }
}

function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

function escapeJs(text) {
    if (!text) return '';
    return text.replace(/'/g, "\\'").replace(/"/g, '\\"');
}
</script>
@endsection
