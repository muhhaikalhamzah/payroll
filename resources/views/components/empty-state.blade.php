<div class="d-flex flex-column align-items-center justify-content-center p-5 text-center text-muted">
    <div class="mb-3">
        @if(isset($icon))
            {!! $icon !!}
        @else
            <i class="bi bi-inbox fs-1 text-secondary"></i>
        @endif
    </div>
    <h5 class="fw-bold">{{ $title ?? 'No Data Available' }}</h5>
    <p class="text-secondary">{{ $description ?? 'There is currently no data to display in this view.' }}</p>
</div>
