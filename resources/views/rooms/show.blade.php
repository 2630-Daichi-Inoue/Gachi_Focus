@extends('layouts.app')

@section('title', ($space->name ?? 'Room').' | GachiFocus')

@section('content')
@php
  // Prepare image and display name (same logic as reserve page)
  $displayName = $space->name ?? 'Room';
  $rawImage = $space->image ?? null;
  $imgSrc = asset('images/room-b.jpg');
  if ($rawImage) {
      if (str_starts_with($rawImage, 'http://') || str_starts_with($rawImage, 'https://') || str_starts_with($rawImage, 'data:image')) {
          $imgSrc = $rawImage;
      } elseif (str_starts_with($rawImage, 'storage/') || str_st_starts_with($rawImage, 'images/')) {
          $imgSrc = asset($rawImage);
      } else {
          $imgSrc = asset('storage/'.$rawImage);
      }
  }
@endphp

<div class="container-xxl py-5">
  <div class="row g-0 shadow-lg rounded overflow-hidden">

    {{-- Left picture (keep consistent with reserve page) --}}
    <div class="col-lg-6 position-relative">
      <img src="{{ $imgSrc }}" alt="{{ $displayName }}"
           class="img-fluid w-100 h-100 object-fit-cover" style="min-height:420px;">
      <div class="position-absolute top-0 start-0 p-4">
        <div class="display-5 fw-bold text-white text-shadow">{{ $displayName }}</div>
      </div>
    </div>

    {{-- Right detail panel --}}
    <div class="col-lg-6 bg-white p-4 p-md-5">
      {{-- Space info --}}
      <div class="mb-4">
        <h2 class="fw-bold mb-2">{{ $displayName }}</h2>
        <div class="text-muted">{{ $space->location_for_details ?? 'No details available.' }}</div>
      </div>

      {{-- Reservation summary (show only when available) --}}
      @isset($reservation)
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body">
            <h3 class="h5 fw-bold mb-3">Reservation Summary</h3>
            <dl class="row gy-2 mb-0">
              <dt class="col-4 text-muted">Date</dt>
              <dd class="col-8">{{ optional($reservation->date)->format('Y/m/d') ?? '-' }}</dd>

              <dt class="col-4 text-muted">Time</dt>
              <dd class="col-8">{{ $reservation->start_time }} - {{ $reservation->end_time }}</dd>

              <dt class="col-4 text-muted">Type</dt>
              <dd class="col-8">{{ $reservation->type }}</dd>

              <dt class="col-4 text-muted">People</dt>
              <dd class="col-8">{{ $reservation->adults }}</dd>

              <dt class="col-4 text-muted">Facilities</dt>
              <dd class="col-8">
                @if(!empty($reservation->facilities))
                  <div class="d-flex flex-wrap gap-2">
                    @foreach($reservation->facilities as $f)
                      <span class="badge bg-light text-dark border">{{ $f }}</span>
                    @endforeach
                  </div>
                @else
                  <span class="text-muted">None</span>
                @endif
              </dd>

              <dt class="col-4 text-muted">Total</dt>
              <dd class="col-8 fw-semibold">¥{{ number_format((int)($reservation->total_price ?? 0), 0) }}</dd>
            </dl>

            {{-- Action buttons --}}
            <div class="d-flex gap-3 mt-4">
            {{-- Edit button --}}
            <a href="{{ route('reservations.edit', ['reservation' => $reservation->id]) }}"
                class="btn btn-dark btn-lg flex-fill">
                Change reservation
            </a>

           @php $cancelModalId = 'cancelModal-'.$reservation->id; @endphp

            {{-- Cancel button (red) --}}
            <button type="button"
                    class="btn btn-danger btn-lg flex-fill"
                    data-bs-toggle="modal"
                    data-bs-target="#{{ $cancelModalId }}">
            Cancel reservation
            </button>

            {{-- Include modal --}}
            @include('rooms.cancel.modal', [
            'reservation' => $reservation,
            'modalId'     => $cancelModalId,
            'title'       => 'Cancel Reservation',
            ])
            

            </div>

            {{-- Pay (primary) - show only if unpaid or pending --}}
            <div class="d-flex gap-3 mt-4">
            @if(($reservation->payment_status ?? 'unpaid') !== 'paid')
                <form method="POST" action="{{ route('reservations.pay', ['reservation' => $reservation->id]) }}" class="flex-fill">
                  @csrf
                  <button type="submit" class="btn btn-primary btn-lg w-100">
                    Pay with card
                  </button>
                </form>
              @endif
            </div>
          </div>
        </div>
      @else
        {{-- No reservation fallback --}}
        <div class="alert alert-secondary">No reservation data found.</div>
      @endisset

      {{-- Map embed (keep same visual style) --}}
      @if(!empty($space->map_embed))
        <div class="mt-3">
          <div class="ratio ratio-16x9 rounded overflow-hidden border">
            {!! $space->map_embed !!}
          </div>
        </div>
      @endif
    </div>
  </div>
</div>

<style>
  .object-fit-cover { object-fit: cover; }
  .text-shadow { text-shadow: 0 2px 8px rgba(0,0,0,.5); }
</style>
@endsection