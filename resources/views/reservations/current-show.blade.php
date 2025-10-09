@extends('layouts.app')

@section('title', 'Current Reservation')

@section('content')
    <div class="container mt-5 x-data">
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
                    <img src="{{ asset('storage/' . (optional(optional($reservation->workspace)->photos)->first()->path ?? 'images/no-image.png')) }}"
                        alt="{{ $reservation->workspace->name ?? 'No Space Data' }}"
                        style="width:160px; height:110px; object-fit:cover; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1); margin-right:16px;">
                    <div>
                        <h5 class="fw-bold mb-1" style="font-size:1.1rem;">
                            {{ $reservation->workspace->name ?? 'Unknown Space' }}
                        </h5>
                        <p class="mb-1 small text-muted">{{ $reservation->workspace->address ?? '-' }}</p>

                        @if (!empty($reservation->workspace->map_embed))
                            @php
                                $mapQuery = urlencode($reservation->workspace->address ?? '');
                                $mapUrl = "https://www.google.com/maps/search/?api=1&query={$mapQuery}";
                            @endphp

                            <a href="{{ $mapUrl }}" target="_blank"
                                style="color:rgba(84,127,161,1); font-weight:600; font-size:0.9rem; text-decoration:none;">
                                view in a map >
                            </a>
                        @elseif (!empty($reservation->workspace->address))
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($reservation->workspace->address) }}"
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
                        {{ optional($reservation->start_time)->format('M d, Y') }}
                    </p>
                    <p class="mb-0 small" style="color:#555;">
                        {{ optional($reservation->start_time)->format('g:i A') }} -
                        {{ optional($reservation->end_time)->format('g:i A') }}
                    </p>
                </div>

                <!-- Status -->
                <div class="col-md-1 mt-3 mt-md-0 text-md-start" style="margin-left:-10px;">
                    <p class="fw-semibold mb-0" style="color:#000;">
                        {{ ucfirst($reservation->status ?? 'pending') }}
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="col-md-2 text-md-end mt-3 mt-md-0" style="padding-right:25px;">

                    @if (in_array($reservation->status, ['confirmed', 'pending']))
                        <a href="{{ route('reservations.edit', $reservation->id) }}"
                            style="display:block; background-color:rgba(84,127,161,1); color:white; 
                            border:none; border-radius:6px; padding:4px 0; min-width:130px; font-size:0.85rem; 
                            text-decoration:none; margin:0 auto 6px auto; text-align:center;">
                            Change
                        </a>

                        <!-- Cancel Button -->
                        <button type="button" x-data @click="$dispatch('open-modal-{{ $reservation->id }}')"
                            style="display:block; background-color:transparent; color:rgba(166,75,75,1);
                            border:1.5px solid rgba(166,75,75,1); border-radius:6px; padding:4px 0;
                            min-width:130px; font-size:0.85rem; margin:0 auto; text-align:center;">
                            Cancel
                        </button>

                        @push('modals')
                            <div x-data="{ open: false }" x-on:open-modal-{{ $reservation->id }}.window="open = true"
                                x-show="open" x-transition.opacity x-cloak
                                class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-40">
                                <!-- Modal -->
                                <div x-show="open" x-transition.opacity x-cloak
                                    class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-40"
                                    aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                    <div x-show="open" x-transition.duration.200ms.scale.origin.center
                                        class="bg-white rounded-3 shadow-lg text-center"
                                        style="position: absolute;top: 50%; left: 50%; transform: translate(-50%, -50%);
                                        width: 90%;max-width: 420px; padding: 32px 28px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);">

                                        <p class="mb-5"
                                            style="font-size:1.1rem; line-height:1.8; color:#000; font-weight:500;">
                                            Are you sure you want to cancel this reservation?
                                        </p>

                                        <div class="d-flex justify-content-center gap-3 mt-4">
                                            <form method="POST" action="{{ route('reservations.cancel', $reservation->id) }}">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-danger fw-semibold px-4 py-2"
                                                    style="min-width:100px; font-size:0.95rem;">
                                                    Cancel
                                                </button>
                                            </form>

                                            <button type="button" @click="open = false"
                                                class="btn btn-outline-secondary fw-semibold px-4 py-2"
                                                style="min-width:100px; font-size:0.95rem; color:#333; border-color:#ccc;">
                                                Not now
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endpush
                    @endif

                    @if ($reservation->status === 'canceled')
                        <form action="{{ route('spaces.rebook', ['id' => $reservation->space_id]) }}" method="GET">
                            <button type="submit"
                                style="display:block; background-color:rgba(77,124,101,1); color:white; border:none;
                                border-radius:6px; padding:4px 0; min-width:130px; font-size:0.85rem; margin:0 auto; text-align:center;">
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
