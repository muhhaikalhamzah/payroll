<x-app>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="page-header-card mb-4 p-4 rounded shadow-sm">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1 fw-bold">{{ $title }}</h4>
                <p class="text-white-50 mb-0">Upload a CSV or Excel file to import attendance records.</p>
            </div>
            <div>
                <a href="{{ route('attendance-records.index') }}" class="btn btn-light shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="{{ route('attendance-records.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Select File (.csv, .xlsx)</label>
                    <input type="file" name="file" class="form-control" accept=".csv, .xlsx, .xls" required>
                    @error('file') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                    
                    <div class="form-text mt-2">
                        <strong>Expected Columns:</strong> NIK, Tanggal, Jam Masuk, Jam Keluar, Status.<br>
                        <em>Note: The process will run in the background. Please check the attendance list a few moments after uploading.</em>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('attendance-records.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i> Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app>
