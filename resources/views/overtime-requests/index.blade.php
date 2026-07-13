<x-app>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="page-header-card mb-4 p-4 rounded shadow-sm">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1 fw-bold">{{ $title }}</h4>
                <p class="text-white-50 mb-0">Manage Overtime Requests here.</p>
            </div>
            <div>
                <a href="{{ route('overtime-requests.create') }}" class="btn btn-light shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> Add Request
                </a>
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
            @if(isset($overtime_requests) && $overtime_requests->isEmpty())
                <x-empty-state 
                    title="No Overtime Requests" 
                    description="There are no overtime requests found." 
                    icon='<i class="bi bi-clock-history fs-1 text-secondary"></i>' 
                />
            @else
            <div class="table-responsive">
                <table id="data-table" class="table table-hover table-striped align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Duration (Mins)</th>
                            <th>Status</th>
                            <th>Approved By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($overtime_requests ?? [] as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->employee->first_name ?? '-' }} {{ $item->employee->last_name ?? '' }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->date)->format('d M Y') }}</td>
                            <td>{{ $item->duration_minutes }}</td>
                            <td>
                                <span class="badge bg-{{ $item->status == 'APPROVED' ? 'success' : ($item->status == 'REJECTED' ? 'danger' : ($item->status == 'PENDING_MANAGER' ? 'warning' : 'secondary')) }}">
                                    {{ str_replace('_', ' ', $item->status) }}
                                </span>
                            </td>
                            <td>{{ $item->approver->name ?? '-' }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    @can('update', $item)
                                    <a href="{{ route('overtime-requests.edit', $item) }}" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endcan
                                    
                                    @can('delete', $item)
                                    <form action="{{ route('overtime-requests.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
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
            @endif
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
