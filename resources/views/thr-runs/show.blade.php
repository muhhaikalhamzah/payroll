<x-app>
  <x-slot:title>{{ $title }}</x-slot:title>
<div class="pagetitle">
  <h1>{{ $title }}</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{ route('thr-runs.index') }}">THR Runs</a></li>
      <li class="breadcrumb-item active">Details</li>
    </ol>
  </nav>
</div>

<section class="section">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Employee Payslips</h5>
            
            <div class="d-flex gap-2">
                @if($thr_run->status === 'DRAFT')
                    @can('submit-thr-runs')
                    <form action="{{ route('approvals.submit', ['type' => 'thr_run', 'id' => $thr_run->id]) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Submit this payroll for Finance approval?')">Submit to Finance</button>
                    </form>
                    @endcan
                @elseif($thr_run->status === 'PENDING_FINANCE')
                    @can('approve-thr-runs')
                    <form action="{{ route('approvals.approve', ['type' => 'thr_run', 'id' => $thr_run->id]) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Approve this THR Run?')">Approve</button>
                    </form>
                    @endcan
                    @can('reject-thr-runs')
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
                    @endcan
                @elseif($thr_run->status === 'APPROVED')
                    @can('mark-thr-runs-paid')
                    <form action="{{ route('approvals.mark-paid', ['type' => 'thr_run', 'id' => $thr_run->id]) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Mark this payroll as PAID?')">Mark as PAID</button>
                    </form>
                    @endcan
                @endif
                <span class="badge bg-secondary d-flex align-items-center px-3 fs-6">{{ $thr_run->status }}</span>
                @if(in_array($thr_run->status, ['PAID', 'COMPLETED']))
                    @can('export-reports')
                    <a href="{{ route('thr-runs.ebupot', $thr_run->id) }}" class="btn btn-warning"><i class="bi bi-file-earmark-spreadsheet"></i> e-Bupot</a>
                    @endcan
                @endif
            </div>
          </div>
          
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Employee</th>
                <th>Basic Salary</th>
                <th>Allowances</th>
                <th>Deductions</th>
                <th>Net Pay</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($thr_run->payslips as $payslip)
              <tr>
                <td>{{ $payslip->employee->user->first_name }} {{ $payslip->employee->user->last_name }}<br><small class="text-muted">{{ $payslip->employee->nik }}</small></td>
                <td>Rp {{ number_format($payslip->basic_salary, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($payslip->total_allowances, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($payslip->total_deductions, 0, ',', '.') }}</td>
                <td class="fw-bold text-success">Rp {{ number_format($payslip->net_pay, 0, ',', '.') }}</td>
                <td>
                  <div class="d-flex gap-1">
                    <a href="{{ route('payslips.show', $payslip->id) }}" class="btn btn-sm btn-info text-white"><i class="bi bi-file-earmark-text"></i> Slip</a>
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

@push('modals')
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('approvals.reject', ['type' => 'thr_run', 'id' => $thr_run->id]) }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="rejectModalLabel">Reject THR Run</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="comments" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
            <textarea class="form-control" id="comments" name="comments" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Reject Payroll</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endpush
</x-app>
