@extends('layouts.app')

@section('content')
<div class="pagetitle">
  <h1>{{ $title }}</h1>
</div>

<section class="section">
  <div class="row">
    <div class="col-lg-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Leave Balance Details</h5>

          <form action="{{ route('leave-balances.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
              <label for="employee_id" class="form-label">Employee</label>
              <select name="employee_id" id="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                <option value="">Select Employee</option>
                @foreach($employees as $emp)
                  <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                    {{ $emp->employee_id }} - {{ $emp->user->name ?? 'Unknown' }}
                  </option>
                @endforeach
              </select>
              @error('employee_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="leave_type_id" class="form-label">Leave Type</label>
              <select name="leave_type_id" id="leave_type_id" class="form-select @error('leave_type_id') is-invalid @enderror" required>
                <option value="">Select Leave Type</option>
                @foreach($leaveTypes as $type)
                  <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                    {{ $type->name }}
                  </option>
                @endforeach
              </select>
              @error('leave_type_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="year" class="form-label">Year</label>
              <input type="number" class="form-control @error('year') is-invalid @enderror" id="year" name="year" value="{{ old('year', date('Y')) }}" required min="2000" max="2100">
              @error('year')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="balance" class="form-label">Total Balance (Days)</label>
              <input type="number" class="form-control @error('balance') is-invalid @enderror" id="balance" name="balance" value="{{ old('balance', 0) }}" required min="0">
              @error('balance')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="used" class="form-label">Used (Days)</label>
              <input type="number" class="form-control @error('used') is-invalid @enderror" id="used" name="used" value="{{ old('used', 0) }}" required min="0">
              @error('used')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
              <a href="{{ route('leave-balances.index') }}" class="btn btn-secondary">Cancel</a>
              <button type="submit" class="btn btn-primary">Save</button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</section>
@endsection
