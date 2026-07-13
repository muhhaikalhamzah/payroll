<x-app>
  <x-slot:title>Payslip Detail</x-slot:title>
<div class="pagetitle">
  <h1>Payslip</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
      <li class="breadcrumb-item active">Payslip Detail</li>
    </ol>
  </nav>
</div>

<section class="section">
  <div class="row">
    <div class="col-lg-8 mx-auto">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title text-center">PAYSLIP<br><small class="text-muted">{{ date("F", mktime(0, 0, 0, $payslip->payrollRun->period_month, 1)) }} {{ $payslip->payrollRun->period_year }}</small></h5>
          
          <div class="row mb-4">
              <div class="col-sm-6">
                  <strong>Employee:</strong> {{ $payslip->employee->user->first_name }} {{ $payslip->employee->user->last_name }}<br>
                  <strong>NIK:</strong> {{ $payslip->employee->nik }}<br>
                  <strong>Department:</strong> {{ $payslip->employee->department?->name }}<br>
                  <strong>Position:</strong> {{ $payslip->employee->position?->name }}
              </div>
          </div>

          <div class="row">
              <div class="col-md-6">
                  <h6 class="fw-bold border-bottom pb-2">Earnings</h6>
                  <div class="d-flex justify-content-between">
                      <span>Basic Salary</span>
                      <span>Rp {{ number_format($payslip->basic_salary, 0, ',', '.') }}</span>
                  </div>
                  @foreach($payslip->components->where('type', 'allowance') as $allowance)
                  <div class="d-flex justify-content-between">
                      <span>{{ $allowance->name }}</span>
                      <span>Rp {{ number_format($allowance->amount, 0, ',', '.') }}</span>
                  </div>
                  @endforeach
                  <div class="d-flex justify-content-between fw-bold mt-2 pt-2 border-top">
                      <span>Total Earnings</span>
                      <span>Rp {{ number_format($payslip->basic_salary + $payslip->total_allowances, 0, ',', '.') }}</span>
                  </div>
              </div>
              <div class="col-md-6">
                  <h6 class="fw-bold border-bottom pb-2">Deductions</h6>
                  @foreach($payslip->components->where('type', 'deduction') as $deduction)
                  <div class="d-flex justify-content-between text-danger">
                      <span>{{ $deduction->name }}</span>
                      <span>- Rp {{ number_format($deduction->amount, 0, ',', '.') }}</span>
                  </div>
                  @endforeach
                  <div class="d-flex justify-content-between fw-bold mt-2 pt-2 border-top text-danger">
                      <span>Total Deductions</span>
                      <span>- Rp {{ number_format($payslip->total_deductions, 0, ',', '.') }}</span>
                  </div>
              </div>
          </div>

          <div class="row mt-4 bg-light p-3 rounded">
              <div class="col-12 d-flex justify-content-between align-items-center">
                  <h5 class="mb-0 fw-bold">NET PAY</h5>
                  <h4 class="mb-0 fw-bold text-success">Rp {{ number_format($payslip->net_pay, 0, ',', '.') }}</h4>
              </div>
          </div>
          
          <div class="text-center mt-4">
              <button onclick="window.print()" class="btn btn-secondary"><i class="bi bi-printer"></i> Print</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
</x-app>
