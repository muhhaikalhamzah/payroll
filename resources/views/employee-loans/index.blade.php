<x-app>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="pagetitle">
        <h1>{{ $title }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
                <li class="breadcrumb-item active">Employee Loans</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
                            <h5 class="card-title mb-0">Loan Records</h5>
                            @can('submit-employee-loans')
                                <a href="{{ route('employee-loans.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Apply for Loan</a>
                            @endcan
                        </div>

                        @if(session('success'))
                        <div class="alert alert-success bg-success text-light border-0 alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif
                        @if(session('error'))
                        <div class="alert alert-danger bg-danger text-light border-0 alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif

                        <table class="table table-hover datatable">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    @if(auth()->user()->hasRole(['super-admin', 'admin', 'hr-admin', 'finance-admin']))
                                    <th scope="col">Employee</th>
                                    @endif
                                    <th scope="col">Request Date</th>
                                    <th scope="col">Total Amount</th>
                                    <th scope="col">Tenor</th>
                                    <th scope="col">Remaining</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($loans as $loan)
                                <tr>
                                    <th scope="row">{{ $loop->iteration }}</th>
                                    @if(auth()->user()->hasRole(['super-admin', 'admin', 'hr-admin', 'finance-admin']))
                                    <td>{{ $loan->employee->user->name ?? '-' }}</td>
                                    @endif
                                    <td>{{ $loan->request_date->format('Y-m-d') }}</td>
                                    <td>Rp {{ number_format($loan->total_amount, 0, ',', '.') }}</td>
                                    <td>{{ $loan->requested_tenor_months }} mths</td>
                                    <td>Rp {{ number_format($loan->remaining_balance, 0, ',', '.') }}</td>
                                    <td>
                                        @if($loan->status == 'DRAFT')
                                            <span class="badge bg-secondary">Draft</span>
                                        @elseif($loan->status == 'PENDING_FINANCE')
                                            <span class="badge bg-warning">Pending Finance</span>
                                        @elseif($loan->status == 'APPROVED')
                                            <span class="badge bg-info">Approved</span>
                                        @elseif($loan->status == 'REJECTED')
                                            <span class="badge bg-danger">Rejected</span>
                                        @elseif($loan->status == 'DISBURSED')
                                            <span class="badge bg-primary">Disbursed (Active)</span>
                                        @elseif($loan->status == 'COMPLETED')
                                            <span class="badge bg-success">Completed</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('employee-loans.show', $loan) }}" class="btn btn-sm btn-info text-white"><i class="bi bi-eye"></i> View</a>

                                            @can('approve-employee-loans')
                                                @if($loan->status == 'PENDING_FINANCE')
                                                    <form action="{{ route('approvals.approve', ['type' => 'employee_loan', 'id' => $loan->id]) }}" method="POST">
                                                        @csrf
                                                        <button class="btn btn-sm btn-success" type="submit" onclick="return confirm('Approve this loan?')"><i class="bi bi-check-circle"></i> Approve</button>
                                                    </form>
                                                    <form action="{{ route('approvals.reject', ['type' => 'employee_loan', 'id' => $loan->id]) }}" method="POST" id="form-reject-{{$loan->id}}">
                                                        @csrf
                                                        <input type="hidden" name="comments" id="comment-{{$loan->id}}">
                                                        <button class="btn btn-sm btn-danger" type="button" onclick="rejectLoan({{$loan->id}})"><i class="bi bi-x-circle"></i> Reject</button>
                                                    </form>
                                                @endif
                                            @endcan

                                            @can('disburse-employee-loans')
                                                @if($loan->status == 'APPROVED')
                                                    <form action="{{ route('employee-loans.disburse', $loan->id) }}" method="POST">
                                                        @csrf
                                                        <button class="btn btn-sm btn-primary" type="submit" onclick="return confirm('Confirm disbursement? This will activate the auto-deduction.')"><i class="bi bi-cash-stack"></i> Disburse</button>
                                                    </form>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <script>
    function rejectLoan(id) {
        let reason = prompt("Please enter the reason for rejection:");
        if (reason !== null && reason.trim() !== "") {
            document.getElementById('comment-' + id).value = reason;
            document.getElementById('form-reject-' + id).submit();
        } else if (reason !== null) {
            alert("Reason is required to reject.");
        }
    }
    </script>
</x-app>
