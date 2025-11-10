@extends('layouts.app')

@section('title', 'Past Reservation')

@section('content')
    <div class="container mt-5">
        <h2 class="fw-bold mb-4">Reservation History</h2>

        <!-- Header Row -->
        <div class="row fw-semibold text-secondary border-bottom pb-2 text-center">
            <div class="col-md-5 text-md-start">Work Space</div>
            <div class="col-md-2">Date / Time</div>
            <div class="col-md-1" style="margin-left:-10px;">Status</div>
            <div class="col-md-2">Price</div>
            <div class="col-md-2">Action</div>
        </div>

        @forelse ($reservations as $reservation)
            <div class="row align-items-center py-4 border-bottom text-center">
                <!-- Work Space -->
                <div class="col-md-5 d-flex text-md-start align-items-start">
                    <img src="{{ asset('storage/' . (optional(optional($reservation->space)->photos)->first()->path ?? 'images/no-image.png')) }}"
                        alt="{{ $reservation->space->name ?? 'No Space Data' }}"
                        style="width:160px; height:110px; object-fit:cover; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1); margin-right:16px;">
                    <div>
                        <h5 class="fw-bold mb-1" style="font-size:1.05rem;">
                            {{ $reservation->space->name ?? 'Unknown Space' }}
                        </h5>
                        <p class="mb-1 small text-muted">{{ $reservation->space->address ?? '-' }}</p>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($reservation->space->address ?? '') }}"
                            target="_blank"
                            style="color:rgba(84,127,161,1); font-weight:600; font-size:0.9rem; text-decoration:none;">
                            view in a map >
                        </a>
                    </div>
                </div>

                <!-- Date / Time -->
                <div class="col-md-2">
                    <p class="fw-semibold mb-1">{{ \Carbon\Carbon::parse($reservation->start_time)->format('M d, Y') }}</p>
                    <p class="mb-0 small text-muted">
                        {{ \Carbon\Carbon::parse($reservation->start_time)->format('g:i A') }} -
                        {{ \Carbon\Carbon::parse($reservation->end_time)->format('g:i A') }}
                    </p>
                </div>

                <!-- Status -->
                <div class="col-md-1" style="margin-left:-10px;">
                    <p class="fw-semibold mb-0 text-capitalize">
                        {{ $reservation->status ?? 'Pending' }}
                    </p>
                </div>

                <!-- Price -->
                <div class="col-md-2 text-center d-flex flex-column align-items-center justify-content-center">
                    <p class="fw-semibold mb-1" style="font-size:1rem;">
                        ${{ number_format($reservation->total_price ?? ($reservation->space->price ?? 0), 2) }}
                    </p>

                    @if (!empty($reservation->tax_rate))
                        <p class="mb-0 small text-muted">
                            Tax ({{ $reservation->country ?? '—' }}): {{ $reservation->tax_rate }}%
                        </p>
                    @endif

                    <a href="{{ route('reservations.invoice', $reservation->id) }}"
                        style="font-size:0.85rem; color:#000; text-decoration:none; display:flex; align-items:center; margin-top:4px;">
                        <i class="fa-solid fa-download me-1" style="color:#000;"></i>
                        Download invoice
                    </a>
                </div>

                <!-- Action Buttons -->
                <div class="col-md-2 d-flex flex-column align-items-center justify-content-center">
                    <!-- Rebook -->
                    <form action="{{ route('reservations.rebook', ['id' => $reservation->id]) }}" method="GET"
                        class="mb-2">
                        <button type="submit"
                            style="background-color:rgba(77,124,101,1);
                               color:white;
                               border:none;
                               border-radius:6px;
                               padding:2px 0;
                               width:140px;
                               height:30px;
                               font-size:0.85rem;
                               font-weight:500;">
                            Rebook
                        </button>
                    </form>

                    <!-- Write a review -->
                    @if (strtolower($reservation->status) === 'completed')
                        <a href="{{ route('reviews.index', $reservation->space_id) }}"
                            class="d-flex align-items-center justify-content-center fw-semibold"
                            style="color:#2f3640; border:1.4px solid #2f3640; border-radius:6px;
                                padding:6px 0; width:140px; height:32px; font-size:0.86rem;
                                background-color:#f8f9fa; letter-spacing:.02em; transition:all .25s ease; text-decoration:none;">
                                View Reviews
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <p class="text-muted fs-5">You have no past reservations.</p>
            </div>
        @endforelse
    </div>
@endsection
