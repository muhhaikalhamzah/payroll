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
    
    @if(session('import_batch_id'))
        <div class="alert alert-info mt-4" id="import-status-container">
            <strong>Import in progress...</strong> <span id="import-status-text">Processing</span>
            <div id="import-stats" class="mt-1"></div>
            <ul id="import-errors" class="text-danger mt-2 mb-0" style="display:none;"></ul>
        </div>
        
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const batchId = '{{ session('import_batch_id') }}';
                const statusContainer = document.getElementById('import-status-container');
                const statusText = document.getElementById('import-status-text');
                const stats = document.getElementById('import-stats');
                const errorList = document.getElementById('import-errors');
                
                const poll = setInterval(function() {
                    fetch('/attendance-records/import-status/' + batchId)
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'not_found') {
                                clearInterval(poll);
                                return;
                            }
                            
                            stats.innerHTML = `Processed: ${data.success} / ${data.total}`;
                            
                            if (data.status === 'completed' || data.status === 'failed') {
                                clearInterval(poll);
                                statusText.innerText = data.status.toUpperCase();
                                statusContainer.classList.remove('alert-info');
                                statusContainer.classList.add(data.status === 'completed' && data.errors.length === 0 ? 'alert-success' : 'alert-warning');
                                
                                if (data.errors && data.errors.length > 0) {
                                    errorList.style.display = 'block';
                                    data.errors.forEach(err => {
                                        let li = document.createElement('li');
                                        li.innerText = err;
                                        errorList.appendChild(li);
                                    });
                                }
                            }
                        })
                        .catch(err => console.error(err));
                }, 2000);
            });
        </script>
    @endif
</x-app>
