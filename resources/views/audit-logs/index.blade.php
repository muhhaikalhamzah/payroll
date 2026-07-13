<x-app>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover datatable" id="data-table">
                            <thead>
                                <tr>
                                    <th scope="col">Time</th>
                                    <th scope="col">User</th>
                                    <th scope="col">Action</th>
                                    <th scope="col">Model Type</th>
                                    <th scope="col">Model ID</th>
                                    <th scope="col">IP Address</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($auditLogs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>{{ $log->user->name ?? 'System' }}</td>
                                    <td>
                                        @if($log->action == 'CREATED')
                                            <span class="badge bg-success">CREATED</span>
                                        @elseif($log->action == 'UPDATED')
                                            <span class="badge bg-warning">UPDATED</span>
                                        @elseif($log->action == 'DELETED')
                                            <span class="badge bg-danger">DELETED</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $log->action }}</span>
                                        @endif
                                    </td>
                                    <td>{{ class_basename($log->auditable_type) }}</td>
                                    <td>{{ $log->auditable_id }}</td>
                                    <td>{{ $log->ip_address }}</td>
                                    <td>
                                        <a href="{{ route('audit-logs.show', $log) }}" class="btn btn-sm btn-info text-white"><i class="bi bi-eye"></i> View</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            if (!$.fn.DataTable.isDataTable('#data-table')) {
                $('#data-table').DataTable();
            }
        });
    </script>
    @endpush
</x-app>
