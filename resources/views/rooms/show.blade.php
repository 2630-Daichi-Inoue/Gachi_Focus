@extends('layouts.app')

@section('title', ($space->name ?? 'Room').' | GachiFocus')

@section('content')
@php
  // Build display name & image URL
  $displayName = $space->name ?? 'Room';
  $rawImage = $space->image ?? null;
  $imgSrc = asset('images/room-b.jpg');

  if ($rawImage) {
      if (str_starts_with($rawImage, 'http://') || str_starts_with($rawImage, 'https://') || str_starts_with($rawImage, 'data:image')) {
          $imgSrc = $rawImage;
      } elseif (str_starts_with($rawImage, 'storage/') || str_starts_with($rawImage, 'images/')) {
          $imgSrc = asset($rawImage);
      } else {
          $imgSrc = asset('storage/'.$rawImage);
      }
  }
@endphp

<div class="container-xxl py-5">
  <div class="row g-0 shadow-lg rounded overflow-hidden">

    {{-- Left picture (consistent with reserve page) --}}
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

      {{-- Reservation summary (only when available) --}}
      @isset($reservation)
        @php
          // --- Always display the saved total (authoritative) ---
          $savedTotal  = (float)($reservation->total_price ?? 0.0);
          $savedCurr   = strtoupper($reservation->currency ?? 'JPY');
          $savedRegion = strtoupper($reservation->payment_region ?? 'JP');

          // --- Normalize time "HH:MM" for a stable tax-rate lookup ---
          $tFrom = \Illuminate\Support\Str::of($reservation->start_time)->beforeLast(':')->__toString();
          $tTo   = \Illuminate\Support\Str::of($reservation->end_time)->beforeLast(':')->__toString();

          // --- Get tax_rate (only) from Pricing::calc() using same context ---
          $probe = \App\Support\Pricing::calc([
              'space_id'          => $space->id,
              'type'              => $reservation->type,
              'date'              => optional($reservation->date)->toDateString(),
              'time_from'         => $tFrom,
              'time_to'           => $tTo,
              'facilities'        => (array)($reservation->facilities ?? []),
              'country_code'      => $savedRegion ?: ($space->country_code ?? 'JP'),
              'currency_override' => $savedCurr  ?: ($space->currency ?? 'JPY'),
          ]);

          $taxRate = (float)($probe['tax_rate'] ?? 0.0);      // decimal (0.10 = 10%)
          $currency = strtoupper($reservation->currency ?? ($probe['currency'] ?? 'JPY'));

          // --- Derive tax amount from saved total & tax_rate ---
          // total = net * (1 + rate) => tax = total * rate / (1 + rate)
          $rawTax = $taxRate > 0 ? ($savedTotal * $taxRate / (1 + $taxRate)) : 0.0;

          // --- Stripe-style zero-decimal handling for display ---
          $zeroDec = in_array($currency, ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'], true);

          // --- Formatters (simple, currency-aware) ---
          $fmt = function (float $v) use ($currency, $zeroDec) {
              if ($currency === 'JPY') {
                  return '¥' . number_format($v, 0);
              }
              $digits = $zeroDec ? 0 : 2;
              return number_format($v, $digits) . ' ' . $currency;
          };

          // Round tax consistently for display
          $taxAmount = $zeroDec ? (int) round($rawTax) : round($rawTax, 2);
        @endphp

        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body">
            <h3 class="h5 fw-bold mb-3">Reservation Summary</h3>

            <dl class="row gy-2 mb-0">
              <dt class="col-4 text-muted">Date</dt>
              <dd class="col-8">{{ optional($reservation->date)->format('Y/m/d') ?? '-' }}</dd>

              <dt class="col-4 text-muted">Time</dt>
              <dd class="col-8">
                {{ \Carbon\Carbon::parse($reservation->start_time)->format('H:i') }}
                -
                {{ \Carbon\Carbon::parse($reservation->end_time)->format('H:i') }}
              </dd>

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

              {{-- --- Total (use saved total) + tax note derived from tax_rate --- --}}
              <dt class="col-4 text-muted">Total</dt>
              <dd class="col-8 fw-semibold">
                {{ $fmt($savedTotal) }}
                <span class="text-muted">（tax {{ $fmt((float)$taxAmount) }}）</span>
              </dd>
            </dl>

            {{-- Actions --}}
            <div class="d-flex gap-3 mt-4">
              {{-- Edit --}}
              <a href="{{ route('reservations.edit', ['reservation' => $reservation->id]) }}"
                 class="btn btn-dark btn-lg flex-fill">
                Change reservation
              </a>

              @php $cancelModalId = 'cancelModal-'.$reservation->id; @endphp

              {{-- Cancel (modal) --}}
              <button type="button"
                      class="btn btn-danger btn-lg flex-fill"
                      data-bs-toggle="modal"
                      data-bs-target="#{{ $cancelModalId }}">
                Cancel reservation
              </button>

              {{-- Cancel modal include --}}
              @include('rooms.cancel.modal', [
                'reservation' => $reservation,
                'modalId'     => $cancelModalId,
                'title'       => 'Cancel Reservation',
              ])
            </div>

            {{-- Pay (only when not paid) --}}
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

      {{-- Map embed --}}
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
