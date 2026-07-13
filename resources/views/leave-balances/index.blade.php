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
            <h5 class="card-title mb-0">List of Leave Balances</h5>
            <div class="d-flex gap-2">
              <form action="{{ route('leave-balances.index') }}" method="GET" class="d-flex align-items-center gap-2">
                <label for="year" class="form-label mb-0">Year</label>
                <select name="year" id="year" class="form-select form-select-sm" onchange="this.form.submit()">
                  @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                  @endfor
                </select>
              </form>
              <a href="{{ route('leave-balances.create') }}" class="btn btn-primary btn-sm text-nowrap"><i class="bi bi-plus-circle"></i> Add New</a>
            </div>
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
                <th scope="col">Employee</th>
                <th scope="col">Leave Type</th>
                <th scope="col">Total Balance</th>
                <th scope="col">Used</th>
                <th scope="col">Remaining</th>
                <th scope="col">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($leaveBalances as $item)
              <tr>
                <th scope="row">{{ $loop->iteration }}</th>
                <td>{{ $item->employee->user->name ?? '-' }}</td>
                <td>{{ $item->leaveType->name ?? '-' }}</td>
                <td>{{ $item->balance }} Days</td>
                <td>{{ $item->used }} Days</td>
                <td><span class="badge bg-primary">{{ $item->balance - $item->used }} Days</span></td>
                <td>
                  <div class="d-flex gap-1">
                    <a href="{{ route('leave-balances.edit', $item) }}" class="btn btn-warning btn-sm">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('leave-balances.destroy', $item) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this item?');">
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
