<x-app>
  <x-slot:title>{{ $title }}</x-slot:title>
<div class="pagetitle">
  <h1>{{ $title }}</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{ route('payroll-runs.index') }}">Payroll Runs</a></li>
      <li class="breadcrumb-item active">Details</li>
    </ol>
  </nav>
</div>

<section class="section">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Employee Payslips</h5>
          
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
              @foreach($payroll_run->payslips as $payslip)
              <tr>
                <td>{{ $payslip->employee->user->first_name }} {{ $payslip->employee->user->last_name }}<br><small class="text-muted">{{ $payslip->employee->nik }}</small></td>
                <td>Rp {{ number_format($payslip->basic_salary, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($payslip->total_allowances, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($payslip->total_deductions, 0, ',', '.') }}</td>
                <td class="fw-bold text-success">Rp {{ number_format($payslip->net_pay, 0, ',', '.') }}</td>
                <td>
                  <a href="{{ route('payslips.show', $payslip->id) }}" class="btn btn-sm btn-info text-white"><i class="bi bi-file-earmark-text"></i> Slip</a>
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
</x-app>
