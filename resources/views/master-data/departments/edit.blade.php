<x-app>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="page-header-card mb-4 p-4 rounded shadow-sm">
        <h4 class="mb-1 fw-bold">{{ $title }}</h4>
        <p class="text-white-50 mb-0">Update details for this Department.</p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="{{ route('departments.update', $department) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label required">Name</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $department->name ?? '') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control">{{ old('description', $department->description ?? '') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Parent Department</label>
                    <select name="parent_department_id" class="form-control">
                        <option value="">None</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('parent_department_id', $department->parent_department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('departments.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Department</button>
                </div>
            </form>
        </div>
    </div>
</x-app>