@extends('layouts.app')

@section('content')
<div class="pagetitle">
  <h1>{{ $title }}</h1>
</div>

<section class="section">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">List of Leave Types</h5>
            <a href="{{ route('leave-types.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add New</a>
          </div>

          @if(session('success'))
          <div class="alert alert-success bg-success text-light border-0 alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          @endif

          <table class="table table-hover datatable">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">Max Days</th>
                <th scope="col">Carry Forward</th>
                <th scope="col">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($leaveTypes as $item)
              <tr>
                <th scope="row">{{ $loop->iteration }}</th>
                <td>{{ $item->name }}</td>
                <td>{{ $item->max_days }} Days</td>
                <td>
                  @if($item->is_carry_forward)
                    <span class="badge bg-success">Yes</span>
                  @else
                    <span class="badge bg-secondary">No</span>
                  @endif
                </td>
                <td>
                  <div class="d-flex gap-1">
                    <a href="{{ route('leave-types.edit', $item) }}" class="btn btn-warning btn-sm">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('leave-types.destroy', $item) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this item?');">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-danger btn-sm" type="submit">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
