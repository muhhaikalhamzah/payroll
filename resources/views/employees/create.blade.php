<x-app>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="page-header-card mb-4 p-4 rounded shadow-sm">
        <h4 class="mb-1 fw-bold">{{ $title }}</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('employees.index') }}" class="text-white-50 text-decoration-none">Employees</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Add Employee</li>
            </ol>
        </nav>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="{{ route('employees.store') }}" method="POST">
                @csrf
                
                <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">Personal Information</h5>
                
                <div class="row mb-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="nik" class="form-label fw-medium">NIK <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nik" name="nik" value="{{ old('nik') }}" required maxlength="16" minlength="16" placeholder="e.g., 3200000000000001">
                        <div class="form-text">Must be exactly 16 digits.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label fw-medium">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="resign" {{ old('status') == 'resign' ? 'selected' : '' }}>Resign</option>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="tax_status" class="form-label fw-medium">PTKP / Tax Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="tax_status" name="tax_status" required>
                            <option value="TK/0" {{ old('tax_status', 'TK/0') == 'TK/0' ? 'selected' : '' }}>TK/0 (Single, 0 Dependents)</option>
                            <option value="TK/1" {{ old('tax_status') == 'TK/1' ? 'selected' : '' }}>TK/1 (Single, 1 Dependent)</option>
                            <option value="TK/2" {{ old('tax_status') == 'TK/2' ? 'selected' : '' }}>TK/2 (Single, 2 Dependents)</option>
                            <option value="TK/3" {{ old('tax_status') == 'TK/3' ? 'selected' : '' }}>TK/3 (Single, 3 Dependents)</option>
                            <option value="K/0" {{ old('tax_status') == 'K/0' ? 'selected' : '' }}>K/0 (Married, 0 Dependents)</option>
                            <option value="K/1" {{ old('tax_status') == 'K/1' ? 'selected' : '' }}>K/1 (Married, 1 Dependent)</option>
                            <option value="K/2" {{ old('tax_status') == 'K/2' ? 'selected' : '' }}>K/2 (Married, 2 Dependents)</option>
                            <option value="K/3" {{ old('tax_status') == 'K/3' ? 'selected' : '' }}>K/3 (Married, 3 Dependents)</option>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="first_name" class="form-label fw-medium">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label fw-medium">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="email" class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label fw-medium">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}">
                    </div>
                </div>

                <h5 class="fw-bold mb-4 text-primary border-bottom pb-2">Employment Information</h5>

                <div class="row mb-3">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label for="department_id" class="form-label fw-medium">Department <span class="text-danger">*</span></label>
                        <select class="form-select" id="department_id" name="department_id" required>
                            <option value="" selected disabled>Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label for="position_id" class="form-label fw-medium">Position <span class="text-danger">*</span></label>
                        <select class="form-select" id="position_id" name="position_id" required>
                            <option value="" selected disabled>Select Position</option>
                            @foreach($positions as $pos)
                                <option value="{{ $pos->id }}" {{ old('position_id') == $pos->id ? 'selected' : '' }}>{{ $pos->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="hire_date" class="form-label fw-medium">Hire Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="hire_date" name="hire_date" value="{{ old('hire_date') }}" required>
                    </div>
                </div>

                <h5 class="fw-bold mb-4 text-primary border-bottom pb-2 mt-4">User Account Linking</h5>
                
                <div class="mb-4">
                    <div class="form-check form-check-inline mb-3">
                        <input class="form-check-input" type="radio" name="user_action" id="action_none" value="none" {{ old('user_action', 'none') == 'none' ? 'checked' : '' }} onchange="toggleUserOptions()">
                        <label class="form-check-label" for="action_none">No Portal Access</label>
                    </div>
                    <div class="form-check form-check-inline mb-3">
                        <input class="form-check-input" type="radio" name="user_action" id="action_link" value="link_existing" {{ old('user_action') == 'link_existing' ? 'checked' : '' }} onchange="toggleUserOptions()">
                        <label class="form-check-label" for="action_link">Link Existing User</label>
                    </div>
                    <div class="form-check form-check-inline mb-3">
                        <input class="form-check-input" type="radio" name="user_action" id="action_create" value="create_new" {{ old('user_action') == 'create_new' ? 'checked' : '' }} onchange="toggleUserOptions()">
                        <label class="form-check-label" for="action_create">Create New User Account</label>
                    </div>

                    <!-- Link Existing User Section -->
                    <div id="link_user_section" class="card bg-light border-0 p-3 mt-2" style="display: none;">
                        <label for="existing_user_id" class="form-label fw-medium">Select Existing User</label>
                        <select class="form-select" id="existing_user_id" name="existing_user_id">
                            <option value="">-- Choose User --</option>
                            @foreach($unlinkedUsers as $user)
                                <option value="{{ $user->id }}" {{ old('existing_user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Create New User Section -->
                    <div id="create_user_section" class="card bg-light border-0 p-3 mt-2" style="display: none;">
                        <div class="row">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label for="new_user_email" class="form-label fw-medium">Login Email</label>
                                <input type="email" class="form-control" id="new_user_email" name="new_user_email" value="{{ old('new_user_email') }}">
                                <div class="form-text">Will be used for portal login.</div>
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label for="new_user_password" class="form-label fw-medium">Password</label>
                                <input type="password" class="form-control" id="new_user_password" name="new_user_password">
                                <div class="form-text">Min. 8 characters.</div>
                            </div>
                            <div class="col-md-4">
                                <label for="new_user_role_id" class="form-label fw-medium">Portal Role</label>
                                <select class="form-select" id="new_user_role_id" name="new_user_role_id">
                                    <option value="">-- Choose Role --</option>
                                    @php
                                        $roles = \App\Models\Role::all();
                                    @endphp
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ (old('new_user_role_id') == $role->id || (old('user_action') !== 'create_new' && $role->slug == 'employee')) ? 'selected' : '' }}>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-5">
                    <a href="{{ route('employees.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Save Employee</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleUserOptions() {
            const actionNone = document.getElementById('action_none').checked;
            const actionLink = document.getElementById('action_link').checked;
            const actionCreate = document.getElementById('action_create').checked;
            
            document.getElementById('link_user_section').style.display = actionLink ? 'block' : 'none';
            document.getElementById('create_user_section').style.display = actionCreate ? 'block' : 'none';
        }
        
        // Run on load to set initial state
        document.addEventListener('DOMContentLoaded', toggleUserOptions);
    </script>
</x-app>
