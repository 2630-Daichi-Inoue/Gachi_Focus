@extends('layouts.app')

@section('title', 'Notification')

@section('content')

<div class="container mt-4 mx-auto" style="max-width: 900px;">

    <h4 class="mb-3 fw-bold">All Notifications</h4>

    @forelse($notifications as $notification)
        <div class="d-flex justify-content-between align-items-start border-bottom py-3">
            <div class="me-3 flex-grow-1">
                <!-- Date -->
                <div class="text-muted small mb-1">
                    {{ $notification->created_at->format('M d Y') }}
                </div>
                <div>
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

@endsection


