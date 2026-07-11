<div class="row g-3 mb-4">
    <div class="col-md-4 text-center">
        <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('niceadmin/img/noprofil.png') }}"
            alt="Avatar" class="img-fluid rounded-circle border border-3 border-primary" style="max-width: 200px;">
    </div>
    <div class="col-md-8">
        <h4 class="fw-bold mb-3">{{ $user->name }}</h4>
        <div class="mb-3">
            <span class="badge bg-primary fs-6">{{ $user->role }}</span>
        </div>
        <div class="list-group list-group-flush">
            <div class="list-group-item px-0 border-0">
                <div class="row">
                    <div class="col-4 text-muted">
                        <i class='bx bx-envelope me-2'></i>Email
                    </div>
                    <div class="col-8 fw-semibold">
                        {{ $user->email }}
                    </div>
                </div>
            </div>
            <div class="list-group-item px-0 border-0">
                <div class="row">
                    <div class="col-4 text-muted">
                        <i class='bx bx-calendar-plus me-2'></i>Dibuat
                    </div>
                    <div class="col-8">
                        {{ $user->created_at->diffForHumans() }}
                        <small class="text-muted d-block">{{ $user->created_at->format('d M Y, H:i') }}</small>
                    </div>
                </div>
            </div>
            <div class="list-group-item px-0 border-0">
                <div class="row">
                    <div class="col-4 text-muted">
                        <i class='bx bx-calendar-edit me-2'></i>Diubah
                    </div>
                    <div class="col-8">
                        {{ $user->updated_at->diffForHumans() }}
                        <small class="text-muted d-block">{{ $user->updated_at->format('d M Y, H:i') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>