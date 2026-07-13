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
          <h5 class="card-title">Leave Type Details</h5>

          <form action="{{ route('leave-types.update', $leaveType) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
              <label for="name" class="form-label">Name</label>
              <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $leaveType->name) }}" required>
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="max_days" class="form-label">Max Days</label>
              <input type="number" class="form-control @error('max_days') is-invalid @enderror" id="max_days" name="max_days" value="{{ old('max_days', $leaveType->max_days) }}" required min="0">
              @error('max_days')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="is_carry_forward" name="is_carry_forward" {{ old('is_carry_forward', $leaveType->is_carry_forward) ? 'checked' : '' }}>
              <label class="form-check-label" for="is_carry_forward">Carry Forward to next year</label>
            </div>

            <div class="d-flex justify-content-end gap-2">
              <a href="{{ route('leave-types.index') }}" class="btn btn-secondary">Cancel</a>
              <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</section>
</x-app>