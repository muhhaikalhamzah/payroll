<x-app>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="pagetitle">
        <h1>{{ $title }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('employee-loans.index') }}">Employee Loans</a></li>
                <li class="breadcrumb-item active">Details</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Loan Overview</h5>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label">Employee</div>
                            <div class="col-lg-8 col-md-8">{{ $employeeLoan->employee->user->name }}</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label">Request Date</div>
                            <div class="col-lg-8 col-md-8">{{ $employeeLoan->request_date->format('d M Y') }}</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label">Reason</div>
                            <div class="col-lg-8 col-md-8">{{ $employeeLoan->reason }}</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label">Total Amount</div>
                            <div class="col-lg-8 col-md-8">Rp {{ number_format($employeeLoan->total_amount, 0, ',', '.') }}</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label">Tenor</div>
                            <div class="col-lg-8 col-md-8">{{ $employeeLoan->requested_tenor_months }} Months</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label">Monthly Est.</div>
                            <div class="col-lg-8 col-md-8">Rp {{ number_format($employeeLoan->monthly_installment, 0, ',', '.') }}</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label">Remaining Bal.</div>
                            <div class="col-lg-8 col-md-8 fw-bold text-primary">Rp {{ number_format($employeeLoan->remaining_balance, 0, ',', '.') }}</div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-lg-4 col-md-4 label">Status</div>
                            <div class="col-lg-8 col-md-8">
                                @if($employeeLoan->status == 'DRAFT')
                                    <span class="badge bg-secondary">Draft</span>
                                @elseif($employeeLoan->status == 'PENDING_FINANCE')
                                    <span class="badge bg-warning">Pending Finance</span>
                                @elseif($employeeLoan->status == 'APPROVED')
                                    <span class="badge bg-info">Approved</span>
                                @elseif($employeeLoan->status == 'REJECTED')
                                    <span class="badge bg-danger">Rejected</span>
                                @elseif($employeeLoan->status == 'DISBURSED')
                                    <span class="badge bg-primary">Disbursed (Active)</span>
                                @elseif($employeeLoan->status == 'COMPLETED')
                                    <span class="badge bg-success">Completed</span>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Approval & Audit Trail</h5>

                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">Requested</div>
                                    By {{ $employeeLoan->employee->user->name }}
                                </div>
                                <span class="badge bg-secondary rounded-pill">{{ $employeeLoan->created_at->format('d M Y H:i') }}</span>
                            </li>

                            @if($employeeLoan->approved_at)
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">Approved</div>
                                    By {{ $employeeLoan->approver->name ?? 'System' }}
                                </div>
                                <span class="badge bg-info rounded-pill">{{ $employeeLoan->approved_at->format('d M Y H:i') }}</span>
                            </li>
                            @endif

                            @if($employeeLoan->disbursed_at)
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold text-success">Disbursed (Funds Transferred)</div>
                                    By {{ $employeeLoan->disburser->name ?? 'System' }}
                                </div>
                                <span class="badge bg-success rounded-pill">{{ $employeeLoan->disbursed_at->format('d M Y H:i') }}</span>
                            </li>
                            @endif
                        </ul>

                        @if($employeeLoan->remaining_balance <= 0 && $employeeLoan->status === 'COMPLETED')
                        <div class="alert alert-success mt-3">
                            <i class="bi bi-check-circle me-1"></i> Loan fully repaid!
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app>
