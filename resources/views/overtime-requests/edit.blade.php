<x-app>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="page-header-card mb-4 p-4 rounded shadow-sm">
        <h4 class="mb-1 fw-bold">{{ $title }}</h4>
        <p class="text-white-50 mb-0">Update the overtime request details below.</p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="{{ route('overtime-requests.update', $overtime_request) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label class="form-label">Employee</label>
                    <select name="employee_id" class="form-select" required>
                        @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ old('employee_id', $overtime_request->employee_id) == $employee->id ? 'selected' : '' }}>
                            {{ $employee->first_name }} {{ $employee->last_name }}
                        </option>
                        @endforeach
                    </select>
                    @error('employee_id') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="{{ old('date', $overtime_request->date) }}" required>
                    @error('date') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Duration (Minutes)</label>
                    <input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes', $overtime_request->duration_minutes) }}" min="1" required>
                    @error('duration_minutes') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Reason</label>
                    <textarea name="reason" class="form-control" rows="3" required>{{ old('reason', $overtime_request->reason) }}</textarea>
                    @error('reason') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="DRAFT" {{ old('status', $overtime_request->status) == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                        <option value="PENDING_MANAGER" {{ old('status', $overtime_request->status) == 'PENDING_MANAGER' ? 'selected' : '' }}>Pending Manager</option>
                        <option value="APPROVED" {{ old('status', $overtime_request->status) == 'APPROVED' ? 'selected' : '' }}>Approved</option>
                        <option value="REJECTED" {{ old('status', $overtime_request->status) == 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    @error('status') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('overtime-requests.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</x-app>
