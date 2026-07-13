<x-app>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mt-3">Audit Log Detail #{{ $auditLog->id }}</h5>

                    <table class="table table-bordered">
                        <tr>
                            <th width="200">Timestamp</th>
                            <td>{{ $auditLog->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>User</th>
                            <td>{{ $auditLog->user->name ?? 'System' }} ({{ $auditLog->user->email ?? '-' }})</td>
                        </tr>
                        <tr>
                            <th>Action</th>
                            <td>
                                @if($auditLog->action == 'CREATED')
                                    <span class="badge bg-success">CREATED</span>
                                @elseif($auditLog->action == 'UPDATED')
                                    <span class="badge bg-warning">UPDATED</span>
                                @elseif($auditLog->action == 'DELETED')
                                    <span class="badge bg-danger">DELETED</span>
                                @else
                                    <span class="badge bg-secondary">{{ $auditLog->action }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Model Type</th>
                            <td>{{ $auditLog->auditable_type }}</td>
                        </tr>
                        <tr>
                            <th>Model ID</th>
                            <td>{{ $auditLog->auditable_id }}</td>
                        </tr>
                        <tr>
                            <th>IP Address</th>
                            <td>{{ $auditLog->ip_address }}</td>
                        </tr>
                        <tr>
                            <th>User Agent</th>
                            <td>{{ $auditLog->user_agent }}</td>
                        </tr>
                    </table>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6>Old Values</h6>
                            <div class="p-3 bg-light rounded" style="max-height: 400px; overflow-y: auto;">
                                <pre><code>@php
                                    if ($auditLog->old_values) {
                                        echo json_encode($auditLog->old_values, JSON_PRETTY_PRINT);
                                    } else {
                                        echo 'N/A';
                                    }
                                @endphp</code></pre>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>New Values</h6>
                            <div class="p-3 bg-light rounded" style="max-height: 400px; overflow-y: auto;">
                                <pre><code>@php
                                    if ($auditLog->new_values) {
                                        echo json_encode($auditLog->new_values, JSON_PRETTY_PRINT);
                                    } else {
                                        echo 'N/A';
                                    }
                                @endphp</code></pre>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 mb-3">
                        <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app>
