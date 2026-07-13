<x-app>
    <x-slot:title>{{ $title }}</x-slot:title>
<div class="pagetitle">
  <h1>{{ $title }}</h1>
</div>

<section class="section">
  <div class="row">
    <div class="col-lg-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Leave Balance Details</h5>

          <form action="{{ route('leave-balances.update', $leaveBalance) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
              <label class="form-label">Employee</label>
              <input type="text" class="form-control" value="{{ $leaveBalance->employee->employee_id }} - {{ $leaveBalance->employee->user->name ?? 'Unknown' }}" disabled>
            </div>

            <div class="mb-3">
              <label class="form-label">Leave Type</label>
              <input type="text" class="form-control" value="{{ $leaveBalance->leaveType->name }}" disabled>
            </div>

            <div class="mb-3">
              <label class="form-label">Year</label>
              <input type="text" class="form-control" value="{{ $leaveBalance->year }}" disabled>
            </div>

            <div class="mb-3">
              <label for="balance" class="form-label">Total Balance (Days)</label>
              <input type="number" class="form-control @error('balance') is-invalid @enderror" id="balance" name="balance" value="{{ old('balance', $leaveBalance->balance) }}" required min="0">
              @error('balance')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="used" class="form-label">Used (Days)</label>
              <input type="number" class="form-control @error('used') is-invalid @enderror" id="used" name="used" value="{{ old('used', $leaveBalance->used) }}" required min="0">
              @error('used')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
              <a href="{{ route('leave-balances.index') }}" class="btn btn-secondary">Cancel</a>
              <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</section>
</x-app>