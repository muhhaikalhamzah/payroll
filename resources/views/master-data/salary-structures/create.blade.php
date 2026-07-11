<x-app>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="page-header-card mb-4 p-4 rounded shadow-sm">
        <h4 class="mb-1 fw-bold">{{ $title }}</h4>
        <p class="text-white-50 mb-0">Fill in the details below to add a new Salary Structure.</p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="{{ route('salary-structures.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label required">Position</label>
                    <select name="position_id" class="form-control" required>
                        <option value="">-- Select Position --</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}" {{ old('position_id') == $pos->id ? 'selected' : '' }}>{{ $pos->name }} ({{ $pos->department->name ?? 'No Dept' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Base Salary</label>
                    <input type="number" name="base_salary" class="form-control" required min="0" step="0.01" value="{{ old('base_salary') }}">
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('salary-structures.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Salary Structure</button>
                </div>
            </form>
        </div>
    </div>
</x-app>