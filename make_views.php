<?php

$entities = [
    'departments' => ['title' => 'Department', 'route' => 'departments'],
    'positions' => ['title' => 'Position', 'route' => 'positions'],
    'salary-components' => ['title' => 'Salary Component', 'route' => 'salary-components'],
    'salary-structures' => ['title' => 'Salary Structure', 'route' => 'salary-structures'],
];

foreach ($entities as $folder => $meta) {
    $dir = __DIR__ . "/resources/views/master-data/{$folder}";
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    
    // Index View
    $index = <<<BLADE
<x-app>
    <x-slot:title>{{ \$title }}</x-slot:title>

    <div class="page-header-card mb-4 p-4 rounded shadow-sm">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1 fw-bold">{{ \$title }}</h4>
                <p class="text-white-50 mb-0">Manage {$meta['title']} data here.</p>
            </div>
            @can('manage-{$folder}')
            <div>
                <a href="{{ route('{$meta['route']}.create') }}" class="btn btn-light shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> Add {$meta['title']}
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
                            <th>Name / Details</th>
                            @can('manage-{$folder}')
                            <th>Action</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(\${$folder} ?? [] as \$item)
                        <tr>
                            <td>{{ \$loop->iteration }}</td>
                            <td>{{ \$item->name ?? \$item->id }}</td>
                            @can('manage-{$folder}')
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('{$meta['route']}.edit', \$item) }}" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('{$meta['route']}.destroy', \$item) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
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
BLADE;
    file_put_contents("$dir/index.blade.php", $index);

    // Create View
    $create = <<<BLADE
<x-app>
    <x-slot:title>{{ \$title }}</x-slot:title>

    <div class="page-header-card mb-4 p-4 rounded shadow-sm">
        <h4 class="mb-1 fw-bold">{{ \$title }}</h4>
        <p class="text-white-50 mb-0">Fill in the details below to add a new {$meta['title']}.</p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="{{ route('{$meta['route']}.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label required">Name / Identifier</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                </div>
                <!-- TODO: Add specific fields for {$meta['title']} here -->
                
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('{$meta['route']}.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save {$meta['title']}</button>
                </div>
            </form>
        </div>
    </div>
</x-app>
BLADE;
    file_put_contents("$dir/create.blade.php", $create);

    // Edit View
    $itemVar = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $folder))));
    if (str_ends_with($itemVar, 's')) {
        $itemVar = substr($itemVar, 0, -1);
    }
    
    $edit = <<<BLADE
<x-app>
    <x-slot:title>{{ \$title }}</x-slot:title>

    <div class="page-header-card mb-4 p-4 rounded shadow-sm">
        <h4 class="mb-1 fw-bold">{{ \$title }}</h4>
        <p class="text-white-50 mb-0">Update details for this {$meta['title']}.</p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="{{ route('{$meta['route']}.update', \$$itemVar) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label required">Name / Identifier</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', \$$itemVar->name ?? '') }}">
                </div>
                <!-- TODO: Add specific fields for {$meta['title']} here -->
                
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('{$meta['route']}.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update {$meta['title']}</button>
                </div>
            </form>
        </div>
    </div>
</x-app>
BLADE;
    file_put_contents("$dir/edit.blade.php", $edit);
}

echo "Views generated successfully.\n";
