@extends('layouts.app')

@section('title', 'Current Reservation')

@section('content')
    <div class="container mt-5">

        @if (session('success'))
            <div class="alert alert-success text-center fw-semibold" style="border-radius:10px;">
                {{ session('success') }}
            </div>
        @endif

        <h2 class="fw-bold mb-4">My Reservations</h2>

        <div class="row fw-semibold text-secondary mb-3 pb-2 border-bottom">
            <div class="col-md-7 text-md-start">Work Space</div>
            <div class="col-md-2 text-md-start" style="margin-left:-15px;">Date / Time</div>
            <div class="col-md-1 text-md-start" style="margin-left:-10px;">Status</div>
            <div class="col-md-2 text-md-center" style="padding-right:25px;">Action</div>
        </div>

        @forelse ($reservations as $reservation)
            <div class="row align-items-center py-4 border-bottom">
                <!-- Work Space Info -->
                <div class="col-md-7 d-flex align-items-start">
                    <img src="{{ asset('storage/' . (optional(optional($reservation->space)->photos)->first()->path ?? 'images/no-image.png')) }}"
                        alt="{{ $reservation->space->name ?? 'No Space Data' }}"
                        style="width:160px; height:110px; object-fit:cover; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1); margin-right:16px;">
                    <div>
                        <h5 class="fw-bold mb-1" style="font-size:1.1rem;">
                            {{ $reservation->space->name ?? 'Unknown Space' }}
                        </h5>
                        <p class="mb-1 small text-muted">{{ $reservation->space->address ?? '-' }}</p>

                        @if (!empty($reservation->space->address))
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($reservation->space->address) }}"
                                target="_blank"
                                style="color:rgba(84,127,161,1); font-weight:600; font-size:0.9rem; text-decoration:none;">
                                view in a map >
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Date / Time -->
                <div class="col-md-2 mt-3 mt-md-0 text-md-start" style="margin-left:-15px;">
                    <p class="fw-semibold mb-1" style="font-size:0.95rem;">
                        {{ $reservation->date->format('M d, Y') }}
                    </p>
                    <p class="mb-0 small" style="color:#555;">
                        {{ $reservation->start_time->format('g:i A') }} -
                        {{ $reservation->end_time->format('g:i A') }}
                    </p>
                </div>

                <!-- Status -->
                <div class="col-md-1 mt-3 mt-md-0 text-md-start" style="margin-left:-10px;">
                    <p class="fw-semibold mb-0" style="color:#000;">
                        {{ ucfirst($reservation->payment_status ?? 'unpaid') }}
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="col-md-2 text-md-end mt-3 mt-md-0" style="padding-right:25px;">

                    @if (in_array($reservation->payment_status, ['confirmed', 'unpaid']))
                        <!-- Change button -->
                        <a href="{{ route('reservations.edit', $reservation->id) }}"
                            style="display:block; width:100%; min-width:130px; padding:6px 0; border-radius:6px;
                        background-color:rgba(84,127,161,1); color:white; border:none; font-size:0.85rem;
                        text-align:center; font-weight:600; margin-bottom:6px; text-decoration:none;">
                            Change
                        </a>

                        <!-- Cancel　button -->
                        <button type="button"
                            style="display:block; width:100%; min-width:130px; padding:4px 0; border-radius:6px;
                            background:transparent; color:rgba(166,75,75,1); border:1.5px solid rgba(166,75,75,1);
                            font-size:0.85rem; font-weight:600; text-align:center;"
                            data-bs-toggle="modal" data-bs-target="#cancelModal-{{ $reservation->id }}">
                            Cancel
                        </button>

                        <div class="modal fade" id="cancelModal-{{ $reservation->id }}" tabindex="-1"
                            aria-labelledby="cancelModalLabel-{{ $reservation->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content text-center p-4" style="border-radius:14px;">
                                    <h5 id="cancelModalLabel-{{ $reservation->id }}" class="mb-3 fw-semibold">
                                        Are you sure you want to cancel this reservation?
                                    </h5>
                                    <div class="d-flex justify-content-center gap-3 mt-2">
                                        <form method="POST" action="{{ route('reservations.cancel', $reservation->id) }}">
                                            @csrf
                                            <button type="submit"
                                                style="padding:6px 20px; background-color:#dc3545; color:white;
                                            border:none; border-radius:6px; font-weight:600;">
                                                Cancel
                                            </button>
                                        </form>
                                        <button type="button"
                                            style="padding:6px 20px; background-color:#6c757d; color:white;
                                        border:none; border-radius:6px; font-weight:600;"
                                            data-bs-dismiss="modal">
                                            Not now
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($reservation->payment_status === 'canceled')
                        <!-- Rebook button -->
                        <form action="{{ route('reservations.rebook', ['id' => $reservation->id]) }}" method="GET">
                            <button type="submit"
                                style="display:block; width:100%; min-width:130px; padding:6px 0; border-radius:6px;
                            background-color:rgba(77,124,101,1); color:white; border:none;
                            font-size:0.85rem; font-weight:600; text-align:center;">
                                Rebook
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <p class="text-muted fs-5">You have no reservations.</p>
            </div>
        @endforelse
    </div>
@endsection
