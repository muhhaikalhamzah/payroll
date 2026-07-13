@extends('layouts.app')

@section('content')
<div class="pagetitle">
  <h1>{{ $title }}</h1>
</div>

<section class="section">
  <div class="row">
    <!-- Current Balances -->
    <div class="col-lg-4">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">My Leave Balances ({{ date('Y') }})</h5>
          <ul class="list-group">
            @forelse($balances as $balance)
              @php
                  // calculate pending 
                  $pendingDuration = \App\Models\LeaveRequest::where('employee_id', $employee->id)
                      ->where('leave_type_id', $balance->leave_type_id)
                      ->whereIn('status', ['DRAFT', 'PENDING_MANAGER', 'PENDING_HR'])
                      ->get()
                      ->sum(function($req) {
                          return \Carbon\Carbon::parse($req->start_date)->diffInDaysFiltered(function(\Carbon\Carbon $date) {
                              return !$date->isWeekend();
                          }, \Carbon\Carbon::parse($req->end_date)) + 1;
                      });
                  $available = $balance->balance - $balance->used - $pendingDuration;
              @endphp
              <li class="list-group-item d-flex justify-content-between align-items-center">
                {{ $balance->leaveType->name }}
                <span class="badge bg-primary rounded-pill">{{ $available }} Days</span>
              </li>
            @empty
              <li class="list-group-item">No balances available.</li>
            @endforelse
          </ul>
        </div>
      </div>
    </div>

    <!-- Application Form -->
    <div class="col-lg-8">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Leave Application Form</h5>

          @if(session('error'))
            <div class="alert alert-danger bg-danger text-light border-0 alert-dismissible fade show" role="alert">
              {{ session('error') }}
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          <form action="{{ route('leave-requests.store') }}" method="POST">
            @csrf
            
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

            <div class="row mb-3">
              <div class="col-md-6">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date') }}" required min="{{ date('Y-m-d') }}">
                @error('start_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date') }}" required min="{{ date('Y-m-d') }}">
                @error('end_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="mb-3">
              <label for="reason" class="form-label">Reason</label>
              <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="3" required>{{ old('reason') }}</textarea>
              @error('reason')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
              <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary">Cancel</a>
              <button type="submit" class="btn btn-primary">Save as Draft</button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</section>
@endsection
