@extends('layouts.app')

@section('title', 'Notification')

@section('content')

<div class="container mt-4 mx-auto" style="max-width: 900px;">

    <h4 class="mb-3 fw-bold">All Notifications</h4>

    @forelse($notifications as $notification)
        <div 
        class="notification-item d-flex justify-content-between align-items-start border-bottom py-3 {{ $notification->read_at ? '' : 'bg-light' }}" data-id="{{ $notification->id }}" data-read="{{ $notification->read_at ? '1' : '0' }}" data-read-url="{{ route('notifications.read', $notification->id) }}" style="cursor: pointer;">

            <div class="me-3 flex-grow-1">
                <!-- Date -->
                <div class="text-muted small mb-1">
                    {{ $notification->created_at->format('M d Y') }}
                </div>
                <div class="notification-message {{ $notification->read_at ? '' : 'fw-bold' }}">
                    <!-- Dot of unread -->
                    <span class="unread-dot {{ $notification->read_at ? 'd-none' : '' }}" style="display:inline-block;width:10px; height:10px; background-color:#0d6efd; border-radius:50%; margin-right:8px; "></span>
                    
                    <!-- The contents of notification -->
                    {{ $notification->message }}
                </div>
            </div>

            <!-- Go to page button -->
            <div>
                @if($notification->type === 'Reservation Approved')
                    <a href="#" class="btn btn-color btn-sm text-white"><i class="fa-solid fa-arrow-up-right-from-square"></i> Go to page</a>
                @elseif($notification->type === 'Cancelation Approved')
                    <a href="#" class="btn btn-color btn-sm text-white"><i class="fa-solid fa-arrow-up-right-from-square"></i> Go to page</a>
                @elseif($notification->type === 'Review Request')
                    <a href="#" class="btn btn-color btn-sm text-white"><i class="fa-solid fa-arrow-up-right-from-square"></i> Go to page</a>
                @endif
            </div>
            
        </div>
    @empty
        <p>No notifications found.</p>
    @endforelse

    <!-- Pagination -->
    <div class="mt-3 d-flex justify-content-center">
        {{ $notifications->links('vendor.pagination.custom-pagination') }}
    </div>
</div>

<!-- Mark as read on click -->
<script>
    document.addEventListener('DOMContentLoaded', function(){
        document.querySelectorAll('.notification-item').forEach(function(item){
            item.addEventListener('click', function(e){
                if(e.target.closest('a, button')){
                    return;
                }

                var isRead = item.dataset.read === '1';
                if(isRead) return; // Do nothing if already read

                var url = item.dataset.readUrl;
                if(!url) return;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN' : '{{ csrf_token() }}',
                        'Accept' : 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(function (data){
                    // change UI as read
                    item.dataset.read = '1';
                    item.classList.remove('bg-light');

                    // remove bold
                    var msg = item.querySelector('.notification-message');
                    if(msg) msg.classList.remove('fw-bold');

                    // remove unread dot
                    var dot = item.querySelector('.unread-dot');
                    if(dot) dot.classList.add('d-none');
                })
                .catch(function (err){
                    console.error('Failed to mark as read:', err);
                });
            });
        });
    });
</script>

@endsection


