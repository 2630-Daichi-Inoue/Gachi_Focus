@extends('layouts.app')

@section('title', ($space->name ?? 'Reserve').' | GachiFocus')

@section('content')
@php
    // Setup image and display name
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
    $minCap = $space->min_capacity ?? 1;
    $maxCap = $space->max_capacity ?? 10;

    // Optional: pass country/currency defaults to JS to keep tax logic aligned with show page
    $defaultCountry = $space->country_code ?? 'JP';
    $defaultCurrency = $space->currency ?? 'JPY';
@endphp

<div 
  x-data="reservePage({
    csrf: '{{ csrf_token() }}',
    quoteUrl: '{{ route('pricing.quote') }}',
    spaceId: {{ json_encode($space->id) }},
    country: '{{ $defaultCountry }}',
    currency: '{{ $defaultCurrency }}',
  })"
  class="container-xxl py-5">

  <div class="row g-0 shadow-lg rounded overflow-hidden">

    {{-- Left image --}}
    <div class="col-lg-6 position-relative">
      <img src="{{ $imgSrc }}" alt="{{ $displayName }}"
           class="img-fluid w-100 h-100 object-fit-cover" style="min-height:420px;">
      <div class="position-absolute top-0 start-0 p-4">
        <div class="display-5 fw-bold text-white text-shadow">{{ $displayName }}</div>
      </div>
    </div>

    {{-- Right form section --}}
    <div class="col-lg-6 bg-white p-4 p-md-5">
      {{-- Main reservation form --}}
      <form method="POST" 
            action="{{ route('rooms.reserve.submit', ['space' => $space->id]) }}" 
            x-on:submit="injectTotal()" 
            class="mb-4">
        @csrf

        {{-- Hidden fields for backend (keep server as source of truth) --}}
        <input type="hidden" name="start_time" :value="time_from">
        <input type="hidden" name="end_time"   :value="time_to">
        <input type="hidden" name="space_id" value="{{ $space->id }}">
        <input type="hidden" name="total_price" :value="total">

        <h2 class="fw-bold mb-4">Reserve {{ $displayName }}</h2>

        {{-- Type --}}
        <div class="mb-3">
          <label class="form-label small text-uppercase text-muted">Type</label>
          <select name="type" class="form-select" x-model="type" x-on:change="recalc()" required>
            <option value="" disabled selected>Select type</option>
            @foreach($types as $t)
              <option value="{{ $t }}">{{ $t }}</option>
            @endforeach
          </select>
        </div>

        {{-- Date --}}
        @php
          $today = \Carbon\Carbon::today()->toDateString();
        @endphp
        <div class="mb-3">
          <label class="form-label small text-uppercase text-muted">Date</label>
          <input
            type="date"
            name="date"
            class="form-control"
            x-model="date"
            x-on:change="recalc()"
            min="{{ $today }}"   {{-- block past dates in UI --}}
            required
          >
        </div>

        {{-- Time --}}
        <div class="row g-4 mb-3">
          <div class="col-6">
            <label class="form-label small text-uppercase text-muted">Time (From)</label>
            <select name="time_from" class="form-select" x-model="time_from" x-on:change="recalc()" required>
              <option value="" disabled selected>Select time</option>
              @foreach($fromTimes as $val)
                <option value="{{ $val }}">{{ $val }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-6">
            <label class="form-label small text-uppercase text-muted">Time (To)</label>
            <select name="time_to" class="form-select" x-model="time_to" x-on:change="recalc()" required>
              <option value="" disabled selected>Select time</option>
              @foreach($toTimes as $val)
                <option value="{{ $val }}">{{ $val }}</option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- Number of people --}}
        <div class="mb-2">
          <label class="form-label small text-uppercase text-muted">Number of people</label>
          <select name="adults" class="form-select" x-model.number="adults" x-on:change="recalc()" required>
            @for($i = max(1, $minCap); $i <= max($minCap, $maxCap); $i++)
              <option value="{{ $i }}">{{ $i }}</option>
            @endfor
          </select>
          <div class="form-text">Capacity: {{ $minCap }}–{{ $maxCap }} ppl</div>
        </div>

        {{-- Facilities --}}
        <div class="mb-4">
          <label class="form-label small text-uppercase text-muted d-block mb-2">Facilities</label>
          <div class="row">
            @foreach(($facilityOptions ?? []) as $f)
              <div class="col-sm-6 mb-2">
                <div class="form-check">
                  <input class="form-check-input" 
                         type="checkbox" 
                         value="{{ $f }}"
                         name="facilities[]" 
                         x-model="facilities" 
                         x-on:change="recalc()" 
                         id="f-{{ Str::slug($f) }}">
                  <label class="form-check-label" for="f-{{ Str::slug($f) }}">{{ $f }}</label>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        {{-- Buttons --}}
        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-secondary btn-lg">Reserve</button>
        </div>
      </form>

      {{-- Summary card --}}
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h3 class="h4 fw-bold mb-3">
            <span x-text="type || 'Type'"></span>
          </h3>

          <dl class="row gy-2">
            <dt class="col-3 text-muted">Date</dt>
            <dd class="col-9">
              <span x-text="formatDate(date)"></span>
              <template x-if="time_from && time_to">
                <span class="ms-2">
                  <span x-text="time_from"></span> - 
                  <span x-text="time_to"></span>
                </span>
              </template>
            </dd>

            <dt class="col-3 text-muted">Number of people</dt>
            <dd class="col-9" x-text="adults || '-'"></dd>

            <dt class="col-3 text-muted">Facilities</dt>
            <dd class="col-9">
              <template x-if="facilities.length">
                <div class="d-flex flex-wrap gap-2">
                  <template x-for="f in facilities" :key="f">
                    <span class="badge bg-light text-dark border" x-text="f"></span>
                  </template>
                </div>
              </template>
              <template x-if="!facilities.length">
                <span class="text-muted">None</span>
              </template>
            </dd>

            <dt class="col-3 text-muted">Total</dt>
            <dd class="col-9 fw-semibold">
              <span x-show="!busy" x-text="formatMoney(total, currency)"></span>
              <span x-show="!busy" class="text-muted">
                （tax <span x-text="formatMoney(tax, currency)"></span>）
              </span>
              <span x-show="busy" class="text-muted">calculating...</span>
            </dd>
          </dl>

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
  </div>
</div>

<style>
  .object-fit-cover { object-fit: cover; }
  .text-shadow { text-shadow: 0 2px 8px rgba(0,0,0,.5); }
</style>

<script>
  // Alpine component for reservation form (tax-inclusive)
  function reservePage(init) {
    return {
      // state
      type: '',
      date: '',
      time_from: '',
      time_to: '',
      adults: 1,
      facilities: [],

      // amounts from quote API
      total: 0,         // tax-included
      tax: 0,           // tax amount
      currency: init.currency || 'JPY',

      // misc
      busy: false,
      csrf: init.csrf,
      quoteUrl: init.quoteUrl,
      spaceId: init.spaceId,
      country: init.country || 'JP',

      // call quote API and update totals
      async recalc() {
        if (!this.type || !this.date || !this.time_from || !this.time_to) return;
        this.busy = true;
        try {
          const res = await fetch(this.quoteUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': this.csrf,
              'Accept': 'application/json',
            },
            body: JSON.stringify({
              space_id: this.spaceId,
              type: this.type,
              date: this.date,
              time_from: this.time_from,
              time_to: this.time_to,
              facilities: this.facilities,
              adults: this.adults,

              // optional hints so Pricing::calc picks same region/currency as show page
              country_code: this.country,
              currency_override: this.currency,
            }),
          });
          if (!res.ok) throw new Error('Quote request failed: ' + res.status);
          const data = await res.json();

          // update amounts (ensure numbers)
          this.total    = Number(data.total || 0);
          this.tax      = Number(data.tax_amount || 0);
          this.currency = data.currency || this.currency;
        } catch (e) {
          console.error(e);
        } finally {
          this.busy = false;
        }
      },

      // inject total before submit (best-effort)
      injectTotal() {
        if (!this.total) this.recalc();
      },

      // formatters
      formatMoney(v, curr) {
        const c = String(curr || 'JPY').toUpperCase();
        const isZero = ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'].includes(c);
        const n = Number(v || 0);
        if (c === 'JPY') {
          return '¥' + n.toLocaleString('ja-JP', { maximumFractionDigits: 0 });
        }
        return n.toLocaleString(undefined, { minimumFractionDigits: isZero ? 0 : 2, maximumFractionDigits: isZero ? 0 : 2 }) + ' ' + c;
      },
      formatDate(iso) {
        if (!iso) return '-';
        const d = new Date(iso + 'T00:00:00');
        if (isNaN(d)) return iso;
        return `${d.getFullYear()}/${String(d.getMonth()+1).padStart(2,'0')}/${String(d.getDate()).padStart(2,'0')}`;
      }
    }
  }
</script>
@endsection
