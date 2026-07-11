<x-app>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="page-header-card mb-4 p-4 rounded shadow-sm">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1 fw-bold">{{ $title }}</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('employees.index') }}" class="text-white-50 text-decoration-none">Employees</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">{{ $employee->first_name }} {{ $employee->last_name }}</li>
                    </ol>
                </nav>
            </div>
            @can('manage-employees')
            <div>
                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-light shadow-sm">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
            </div>
            @endcan
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
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

    <div class="row">
        <!-- Sidebar profile info -->
        <div class="col-xl-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center p-4">
                    <div class="avatar-circle-lg bg-primary bg-opacity-10 text-primary fw-bold mx-auto mb-3">
                        {{ substr($employee->first_name, 0, 1) }}
                    </div>
                    <h5 class="fw-bold mb-1">{{ $employee->first_name }} {{ $employee->last_name }}</h5>
                    <p class="text-muted mb-3">{{ $employee->position?->name ?? 'No Position' }}</p>
                    
                    @if($employee->status === 'active')
                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill mb-4">Active</span>
                    @else
                        <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill mb-4">Resigned</span>
                    @endif

                    <ul class="list-group list-group-flush text-start mt-4">
                        <li class="list-group-item px-0 py-3 d-flex align-items-center">
                            <i class="bi bi-person-badge fs-5 text-muted me-3"></i>
                            <div>
                                <div class="small text-muted">NIK</div>
                                <div class="fw-medium">{{ $employee->nik }}</div>
                            </div>
                        </li>
                        <li class="list-group-item px-0 py-3 d-flex align-items-center">
                            <i class="bi bi-envelope fs-5 text-muted me-3"></i>
                            <div>
                                <div class="small text-muted">Email</div>
                                <div class="fw-medium">{{ $employee->email }}</div>
                            </div>
                        </li>
                        <li class="list-group-item px-0 py-3 d-flex align-items-center">
                            <i class="bi bi-telephone fs-5 text-muted me-3"></i>
                            <div>
                                <div class="small text-muted">Phone</div>
                                <div class="fw-medium">{{ $employee->phone ?? '-' }}</div>
                            </div>
                        </li>
                        <li class="list-group-item px-0 py-3 d-flex align-items-center">
                            <i class="bi bi-building fs-5 text-muted me-3"></i>
                            <div>
                                <div class="small text-muted">Department</div>
                                <div class="fw-medium">{{ $employee->department?->name ?? '-' }}</div>
                            </div>
                        </li>
                        <li class="list-group-item px-0 py-3 d-flex align-items-center border-bottom-0">
                            <i class="bi bi-calendar-check fs-5 text-muted me-3"></i>
                            <div>
                                <div class="small text-muted">Hire Date</div>
                                <div class="fw-medium">{{ \Carbon\Carbon::parse($employee->hire_date)->format('d M Y') }}</div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main content tabs -->
        <div class="col-xl-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white pt-3 pb-0 border-bottom-0">
                    <ul class="nav nav-tabs nav-tabs-custom" id="employeeTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank" type="button" role="tab" aria-controls="bank" aria-selected="true">Bank Accounts</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="salary-tab" data-bs-toggle="tab" data-bs-target="#salary" type="button" role="tab" aria-controls="salary" aria-selected="false">Salary Components</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab" aria-controls="history" aria-selected="false">Position History</button>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body p-4">
                    <div class="tab-content" id="employeeTabsContent">
                        
                        <!-- Bank Accounts Tab -->
                        <div class="tab-pane fade show active" id="bank" role="tabpanel" aria-labelledby="bank-tab">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold mb-0">Bank Accounts</h5>
                                @can('manage-bank-accounts')
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addBankModal">
                                    <i class="bi bi-plus-circle me-1"></i> Add Account
                                </button>
                                @endcan
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Bank Name</th>
                                            <th>Account Name</th>
                                            <th>Account Number</th>
                                            <th>Primary</th>
                                            @can('manage-bank-accounts')
                                            <th class="text-end">Actions</th>
                                            @endcan
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($employee->bankAccounts as $account)
                                        <tr>
                                            <td class="fw-medium">{{ $account->bank_name }}</td>
                                            <td>{{ $account->account_name }}</td>
                                            <td class="font-monospace">
                                                @if(auth()->user()->can('manage-bank-accounts'))
                                                    {{ $account->account_number }}
                                                @else
                                                    ••••{{ substr($account->account_number, -4) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($account->is_primary)
                                                    <span class="badge bg-success"><i class="bi bi-star-fill me-1"></i> Primary</span>
                                                @endif
                                            </td>
                                            @can('manage-bank-accounts')
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-outline-primary me-1" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editBankModal{{ $account->id }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="{{ route('employees.bank-accounts.destroy', [$employee, $account]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this bank account?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>

                                                <!-- Edit Bank Modal -->
                                                <div class="modal fade" id="editBankModal{{ $account->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog text-start">
                                                        <form action="{{ route('employees.bank-accounts.update', [$employee, $account]) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-content border-0 shadow">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title fw-bold">Edit Bank Account</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body p-4">
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-medium">Bank Name</label>
                                                                        <input type="text" class="form-control" name="bank_name" value="{{ $account->bank_name }}" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-medium">Account Name</label>
                                                                        <input type="text" class="form-control" name="account_name" value="{{ $account->account_name }}" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-medium">Account Number</label>
                                                                        <input type="text" class="form-control" name="account_number" value="{{ $account->account_number }}" required>
                                                                    </div>
                                                                    <div class="form-check mt-3">
                                                                        <input class="form-check-input" type="checkbox" name="is_primary" value="1" id="is_primary_{{ $account->id }}" {{ $account->is_primary ? 'checked' : '' }}>
                                                                        <label class="form-check-label" for="is_primary_{{ $account->id }}">
                                                                            Set as Primary Account
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer bg-light border-0">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                            @endcan
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No bank accounts added yet.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Salary Components Tab -->
                        <div class="tab-pane fade" id="salary" role="tabpanel" aria-labelledby="salary-tab">
                            <h5 class="fw-bold mb-4">Salary Components</h5>
                            
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Component Name</th>
                                            <th>Type</th>
                                            <th class="text-end">Amount (IDR)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($employee->salaryComponents as $component)
                                        <tr>
                                            <td class="fw-medium">{{ $component->name }}</td>
                                            <td>
                                                @if($component->type === 'allowance')
                                                    <span class="badge bg-success bg-opacity-10 text-success">Allowance</span>
                                                @else
                                                    <span class="badge bg-danger bg-opacity-10 text-danger">Deduction</span>
                                                @endif
                                            </td>
                                            <td class="text-end fw-medium font-monospace">
                                                {{ number_format($component->pivot->amount, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">No salary components assigned.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                    @if($employee->salaryComponents->isNotEmpty())
                                    <tfoot class="bg-light fw-bold">
                                        <tr>
                                            <td colspan="2" class="text-end">Estimated Basic Pay (Allowances - Deductions)</td>
                                            <td class="text-end font-monospace text-primary">
                                                @php
                                                    $total = $employee->salaryComponents->sum(function($c) {
                                                        return $c->type === 'allowance' ? $c->pivot->amount : -$c->pivot->amount;
                                                    });
                                                @endphp
                                                {{ number_format($total, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>

                        <!-- Position History Tab -->
                        <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
                            <h5 class="fw-bold mb-4">Position History</h5>
                            
                            <div class="timeline">
                                @forelse($employee->positionHistories()->latest('start_date')->get() as $history)
                                <div class="timeline-item pb-4 position-relative border-start border-2 ms-3 ps-4 {{ $loop->first ? 'border-primary' : 'border-secondary' }}">
                                    <div class="timeline-marker position-absolute top-0 start-0 translate-middle rounded-circle border border-2 {{ $loop->first ? 'border-primary bg-primary' : 'border-secondary bg-white' }}" style="width: 16px; height: 16px;"></div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="fw-bold mb-0 {{ $loop->first ? 'text-primary' : '' }}">{{ $history->position->name }}</h6>
                                        <span class="badge {{ $loop->first ? 'bg-primary' : 'bg-secondary' }} bg-opacity-10 text-{{ $loop->first ? 'primary' : 'secondary' }}">
                                            {{ \Carbon\Carbon::parse($history->start_date)->format('M Y') }} - 
                                            {{ $history->end_date ? \Carbon\Carbon::parse($history->end_date)->format('M Y') : 'Present' }}
                                        </span>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-4 text-muted">No position history available.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Bank Modal -->
    @can('manage-bank-accounts')
    <div class="modal fade" id="addBankModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog text-start">
            <form action="{{ route('employees.bank-accounts.store', $employee) }}" method="POST">
                @csrf
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Add Bank Account</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Bank Name</label>
                            <input type="text" class="form-control" name="bank_name" required placeholder="e.g. BCA, Mandiri">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Account Name</label>
                            <input type="text" class="form-control" name="account_name" required value="{{ $employee->first_name }} {{ $employee->last_name }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Account Number</label>
                            <input type="text" class="form-control" name="account_number" required>
                        </div>
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" name="is_primary" value="1" id="is_primary_new" checked>
                            <label class="form-check-label" for="is_primary_new">
                                Set as Primary Account
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Account</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endcan

    <style>
        .avatar-circle-lg {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }
        .nav-tabs-custom .nav-link {
            color: #6c757d;
            border: none;
            border-bottom: 2px solid transparent;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
        }
        .nav-tabs-custom .nav-link.active {
            color: var(--bs-primary);
            border-bottom: 2px solid var(--bs-primary);
            background: transparent;
        }
        .nav-tabs-custom .nav-link:hover:not(.active) {
            border-bottom-color: #dee2e6;
        }
    </style>
</x-app>
