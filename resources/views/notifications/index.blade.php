<x-app>
    <x-slot:title>{{ $title }}</x-slot:title>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
                        <h5 class="card-title mb-0">My Notifications</h5>
                        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">Mark All as Read</button>
                        </form>
                    </div>

                    <div class="list-group">
                        @forelse($notifications as $notification)
                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-start {{ $notification->read_at ? '' : 'list-group-item-info' }}">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">{{ $notification->data['title'] ?? 'Notification' }}</div>
                                    {{ $notification->data['message'] ?? '' }}
                                    <div class="text-muted small mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                                </div>
                                @if(!$notification->read_at)
                                    <form action="{{ route('notifications.mark-read', $notification->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-link text-decoration-none">Mark as read</button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <x-empty-state 
                                title="No Notifications" 
                                description="You're all caught up! There are no new notifications for you." 
                            />
                        @endforelse
                    </div>
                    
                    <div class="mt-3">
                        {{ $notifications->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app>
