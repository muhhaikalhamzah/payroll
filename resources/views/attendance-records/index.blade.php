<x-app>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="page-header-card mb-4 p-4 rounded shadow-sm">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1 fw-bold">{{ $title }}</h4>
                <p class="text-white-50 mb-0">Manage Attendance Records here.</p>
            </div>
            <div>
                @can('create', App\Models\AttendanceRecord::class)
                <a href="{{ route('attendance-records.import-form') }}" class="btn btn-outline-light shadow-sm me-2">
                    <i class="bi bi-upload me-1"></i> Import
                </a>
                <a href="{{ route('attendance-records.create') }}" class="btn btn-light shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> Add Record
                </a>
                @endcan
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="data-table" class="table table-hover table-striped align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances ?? [] as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->employee->first_name ?? '-' }} {{ $item->employee->last_name ?? '' }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->date)->format('d M Y') }}</td>
                            <td>{{ $item->clock_in ?? '-' }}</td>
                            <td>{{ $item->clock_out ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $item->status == 'hadir' ? 'success' : ($item->status == 'alfa' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    @can('update', $item)
                                    <a href="{{ route('attendance-records.edit', $item) }}" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('delete', $item)
                                    <form action="{{ route('attendance-records.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" type="submit">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#data-table').DataTable();
        });
    </script>
    @endpush
</x-app>
