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
            <h5 class="card-title mb-0">Leave Requests</h5>
            @can('submit-leave-requests')
              <a href="{{ route('leave-requests.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Apply for Leave</a>
            @endcan
          </div>

          @if(session('success'))
          <div class="alert alert-success bg-success text-light border-0 alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          @endif
          @if(session('error'))
          <div class="alert alert-danger bg-danger text-light border-0 alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          @endif

          <table class="table table-hover datatable">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Employee</th>
                <th scope="col">Type</th>
                <th scope="col">Start Date</th>
                <th scope="col">End Date</th>
                <th scope="col">Reason</th>
                <th scope="col">Status</th>
                <th scope="col">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($leaveRequests as $item)
              <tr>
                <th scope="row">{{ $loop->iteration }}</th>
                <td>{{ $item->employee->user->name ?? '-' }}</td>
                <td>{{ $item->leaveType->name ?? '-' }}</td>
                <td>{{ $item->start_date->format('Y-m-d') }}</td>
                <td>{{ $item->end_date->format('Y-m-d') }}</td>
                <td>{{ Str::limit($item->reason, 30) }}</td>
                <td>
                  @if($item->status == 'DRAFT')
                    <span class="badge bg-secondary">Draft</span>
                  @elseif($item->status == 'PENDING_MANAGER')
                    <span class="badge bg-warning">Pending Manager</span>
                  @elseif($item->status == 'PENDING_HR')
                    <span class="badge bg-info">Pending HR</span>
                  @elseif($item->status == 'APPROVED')
                    <span class="badge bg-success">Approved</span>
                  @elseif($item->status == 'REJECTED')
                    <span class="badge bg-danger">Rejected</span>
                  @endif
                </td>
                <td>
                  <div class="d-flex gap-1">
                    @if($item->status == 'DRAFT' && $item->employee->user_id == auth()->id())
                      <form action="{{ route('approvals.submit', ['type' => 'leave_request', 'id' => $item->id]) }}" method="POST">
                        @csrf
                        <button class="btn btn-sm btn-success" type="submit" onclick="return confirm('Submit this request?')"><i class="bi bi-send"></i> Submit</button>
                      </form>
                      <form action="{{ route('leave-requests.destroy', $item) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('Delete this draft?')"><i class="bi bi-trash"></i></button>
                      </form>
                    @endif

                    @can('approve-leave-requests')
                      @if($item->status == 'PENDING_MANAGER' || $item->status == 'PENDING_HR')
                        <form action="{{ route('approvals.approve', ['type' => 'leave_request', 'id' => $item->id]) }}" method="POST">
                          @csrf
                          <button class="btn btn-sm btn-success" type="submit" onclick="return confirm('Approve this request?')"><i class="bi bi-check-circle"></i> Approve</button>
                        </form>
                        <!-- Reject requires comment, usually via modal, but for simplicity we pass a generic comment or use a prompt -->
                        <form action="{{ route('approvals.reject', ['type' => 'leave_request', 'id' => $item->id]) }}" method="POST" id="form-reject-{{$item->id}}">
                          @csrf
                          <input type="hidden" name="comments" id="comment-{{$item->id}}">
                          <button class="btn btn-sm btn-danger" type="button" onclick="rejectRequest({{$item->id}})"><i class="bi bi-x-circle"></i> Reject</button>
                        </form>
                      @endif
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
  </div>
</section>

<script>
function rejectRequest(id) {
    let reason = prompt("Please enter the reason for rejection:");
    if (reason !== null && reason.trim() !== "") {
        document.getElementById('comment-' + id).value = reason;
        document.getElementById('form-reject-' + id).submit();
    } else if (reason !== null) {
        alert("Reason is required to reject.");
    }
}
</script>
@endsection
