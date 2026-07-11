<x-app>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="page-header-card mb-4 p-4 rounded shadow-sm">
        <h4 class="mb-1 fw-bold">{{ $title }}</h4>
        <p class="text-white-50 mb-0">Update details for this Salary Component.</p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="{{ route('salary-components.update', $salaryComponent) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label required">Name</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $salaryComponent->name ?? '') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label required">Type</label>
                    <select name="type" class="form-control" required>
                        <option value="allowance" {{ old('type', $salaryComponent->type ?? '') == 'allowance' ? 'selected' : '' }}>Allowance</option>
                        <option value="deduction" {{ old('type', $salaryComponent->type ?? '') == 'deduction' ? 'selected' : '' }}>Deduction</option>
                    </select>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_fixed" class="form-check-input" id="is_fixed" value="1" {{ old('is_fixed', $salaryComponent->is_fixed ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_fixed">Is Fixed Amount?</label>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_taxable" class="form-check-input" id="is_taxable" value="1" {{ old('is_taxable', $salaryComponent->is_taxable ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_taxable">Is Taxable?</label>
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('salary-components.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Salary Component</button>
                </div>
            </form>
        </div>
    </div>
</x-app>