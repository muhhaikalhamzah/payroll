<x-app>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="pagetitle">
        <h1>{{ $title }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('employee-loans.index') }}">Employee Loans</a></li>
                <li class="breadcrumb-item active">Apply</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Loan Application Form</h5>

                        @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <form action="{{ route('employee-loans.store') }}" method="POST">
                            @csrf

                            <div class="row mb-3">
                                <label for="employee_name" class="col-sm-3 col-form-label">Employee</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="employee_name" value="{{ $employee->user->name }}" disabled>
                                    <small class="text-muted">Your maximum eligible loan limit is: Rp {{ number_format($maxLimit, 0, ',', '.') }}</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="reason" class="col-sm-3 col-form-label">Reason</label>
                                <div class="col-sm-9">
                                    <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" rows="3" required>{{ old('reason') }}</textarea>
                                    @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="total_amount" class="col-sm-3 col-form-label">Amount (Rp)</label>
                                <div class="col-sm-9">
                                    <input type="number" name="total_amount" id="total_amount" class="form-control @error('total_amount') is-invalid @enderror" value="{{ old('total_amount') }}" min="100000" max="{{ $maxLimit }}" step="1000" required>
                                    @error('total_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="requested_tenor_months" class="col-sm-3 col-form-label">Tenor (Months)</label>
                                <div class="col-sm-9">
                                    <input type="number" name="requested_tenor_months" id="requested_tenor_months" class="form-control @error('requested_tenor_months') is-invalid @enderror" value="{{ old('requested_tenor_months', 1) }}" min="1" max="36" required>
                                    @error('requested_tenor_months') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label">Est. Installment</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" id="est_installment" class="form-control" value="0" readonly>
                                        <span class="input-group-text">/ month</span>
                                    </div>
                                    <small class="text-muted">The final installment might be slightly adjusted due to rounding.</small>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Submit Application</button>
                                <a href="{{ route('employee-loans.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const amountInput = document.getElementById('total_amount');
            const tenorInput = document.getElementById('requested_tenor_months');
            const estInput = document.getElementById('est_installment');

            function calculateInstallment() {
                const amount = parseFloat(amountInput.value);
                const tenor = parseInt(tenorInput.value);

                if (!isNaN(amount) && !isNaN(tenor) && tenor > 0) {
                    const est = Math.ceil(amount / tenor);
                    estInput.value = new Intl.NumberFormat('id-ID').format(est);
                } else {
                    estInput.value = '0';
                }
            }

            amountInput.addEventListener('input', calculateInstallment);
            tenorInput.addEventListener('input', calculateInstallment);
            calculateInstallment(); // initial run
        });
    </script>
</x-app>
