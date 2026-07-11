<x-app>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="page-header-card mb-4 p-4 rounded shadow-sm">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1 fw-bold">{{ $title }}</h4>
                <p class="text-white-50 mb-0">Manage Position data here.</p>
            </div>
            @can('manage-positions')
            <div>
                <a href="{{ route('positions.create') }}" class="btn btn-light shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> Add Position
                </a>
            </div>
            @endcan
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
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
                            <th>Name</th>
                            <th>Department</th>
                            @can('manage-positions')
                            <th>Action</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($positions ?? [] as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->department->name ?? '-' }}</td>
                            @can('manage-positions')
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('positions.edit', $item) }}" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('positions.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" type="submit">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endcan
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