@extends('layouts.app')

@section('title', 'Edit '.($space->name ?? 'Reservation').' | GachiFocus')

@section('content')
@php
    use Illuminate\Support\Str;

    // --- Image source ---
    $displayName = $space->name ?? 'Room';
    $rawImage = $space->image ?? null;
    $imgSrc = asset('images/room-b.jpg');
    if ($rawImage) {
        if (str_starts_with($rawImage, 'http') || str_starts_with($rawImage, 'https://') || str_starts_with($rawImage, 'data:image')) {
            $imgSrc = $rawImage;
        } elseif (str_starts_with($rawImage, 'storage/') || str_starts_with($rawImage, 'images/')) {
            $imgSrc = asset($rawImage);
        } else {
            $imgSrc = asset('storage/'.$rawImage);
        }
    }

    // --- Capacity range ---
    $minCap = $space->min_capacity ?? 1;
    $maxCap = $space->max_capacity ?? 10;

    // --- Let server decide currency by country (do not force override) ---
    $defaultCountry = $space->country_code ?? 'JP';
@endphp

<div
  x-data="editPage({
    csrf: '{{ csrf_token() }}',
    quoteUrl: '{{ route('pricing.quote') }}',
    spaceId: {{ json_encode($space->id) }},
    type: @js($defaultType),
    date: @js($defaultDate),
    start_time: @js($defaultStart),
    end_time: @js($defaultEnd),
    adults: @js($defaultAdults),
    facilities: @js($defaultFacilities),
    country: '{{ $defaultCountry }}',
  })"
  x-init="recalc()"
  class="container-xxl py-5">

  <div class="row g-0 shadow-lg rounded overflow-hidden">

    {{-- Left: Image --}}
    <div class="col-lg-6 position-relative">
      <img src="{{ $imgSrc }}" alt="{{ $displayName }}"
           class="img-fluid w-100 h-100 object-fit-cover" style="min-height:420px;">
      <div class="position-absolute top-0 start-0 p-4">
        <div class="display-5 fw-bold text-white text-shadow">{{ $displayName }}</div>
      </div>
    </div>

    {{-- Right: Form --}}
    <div class="col-lg-6 bg-white p-4 p-md-5">
      <form method="POST" action="{{ route('reservations.update', $reservation) }}" class="mb-4">
        @csrf
        @method('PUT')

        {{-- Hidden inputs --}}
        <input type="hidden" name="space_id" value="{{ $space->id }}">
        <input type="hidden" name="total_price" :value="total">

        <h2 class="fw-bold mb-4">Edit {{ $displayName }} Reservation</h2>

        {{-- Type --}}
        <div class="mb-3">
          <label class="form-label small text-uppercase text-muted">Type</label>
          <select name="type" class="form-select" x-model="type" x-on:change="recalc()" required>
            <option value="" disabled>Select type</option>
            @foreach($types as $t)
              <option value="{{ $t }}" {{ ($defaultType ?? '') === $t ? 'selected' : '' }}>
                {{ $t }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Date --}}
        <div class="mb-3">
          <label class="form-label small text-uppercase text-muted">Date</label>
          <input type="date" name="date" class="form-control" x-model="date" x-on:change="recalc()" required>
        </div>

        {{-- Time --}}
        <div class="row g-4 mb-3">
          <div class="col-6">
            <label class="form-label small text-uppercase text-muted">Time (From)</label>
            <select name="start_time" class="form-select" x-model="start_time" x-on:change="recalc()" required>
              @foreach($fromTimes as $val)
                <option value="{{ $val }}" {{ ($defaultStart ?? '') === $val ? 'selected' : '' }}>
                  {{ $val }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-6">
            <label class="form-label small text-uppercase text-muted">Time (To)</label>
            <select name="end_time" class="form-select" x-model="end_time" x-on:change="recalc()" required>
              @foreach($toTimes as $val)
                <option value="{{ $val }}" {{ ($defaultEnd ?? '') === $val ? 'selected' : '' }}>
                  {{ $val }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- Adults --}}
        <div class="mb-2">
          <label class="form-label small text-uppercase text-muted">Adults</label>
          <select name="adults" class="form-select" x-model.number="adults" x-on:change="recalc()" required>
            @for($i = max(1, $minCap); $i <= max($minCap, $maxCap); $i++)
              <option value="{{ $i }}" {{ ($defaultAdults ?? 1) == $i ? 'selected' : '' }}>
                {{ $i }}
              </option>
            @endfor
          </select>
          <div class="form-text">Capacity: {{ $minCap }}–{{ $maxCap }} ppl</div>
        </div>

        {{-- Facilities --}}
        <div class="mb-4">
          <label class="form-label small text-uppercase text-muted d-block mb-2">Facilities</label>
          <div class="row">
            @foreach(($facilityOptions ?? []) as $f)
              @php $id='f-'.Str::slug($f); @endphp
              <div class="col-sm-6 mb-2">
                <div class="form-check">
                  <input class="form-check-input"
                         type="checkbox"
                         id="{{ $id }}"
                         name="facilities[]"
                         value="{{ $f }}"
                         x-model="facilities"
                         x-on:change="recalc()"
                         @checked(in_array($f, $defaultFacilities, true))>
                  <label class="form-check-label" for="{{ $id }}">{{ $f }}</label>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        {{-- Submit --}}
        <div class="d-grid">
          <button type="submit" class="btn btn-secondary btn-lg">Save Changes</button>
        </div>
      </form>

      {{-- Summary --}}
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h3 class="h4 fw-bold mb-3"><span x-text="type || 'Type'"></span></h3>
          <dl class="row gy-2">
            <dt class="col-3 text-muted">Date</dt>
            <dd class="col-9">
              <span x-text="formatDate(date)"></span>
              <template x-if="displayStart() && displayEnd()">
                <span class="ms-2">
                  <span x-text="displayStart()"></span> - <span x-text="displayEnd()"></span>
                </span>
              </template>
            </dd>
            <dt class="col-3 text-muted">Adults</dt>
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
              <span x-show="!busy" class="text-muted">（tax <span x-text="formatMoney(tax, currency)"></span>）</span>
              <span x-show="busy" class="text-muted">calculating...</span>
            </dd>
          </dl>
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
  // Alpine.js component (tax-inclusive summary)
  function editPage(init) {
    return {
      // ---- props / init ----
      csrf: init.csrf,
      quoteUrl: init.quoteUrl,
      spaceId: init.spaceId,
      type: init.type || '',
      date: init.date || '',
      start_time: init.start_time || '',
      end_time: init.end_time || '',
      adults: init.adults || 1,
      facilities: Array.isArray(init.facilities) ? init.facilities : [],
      country: init.country || 'JP',

      // ---- totals from quote API ----
      total: 0,   // tax-included
      tax: 0,     // tax amount
      currency: 'JPY',

      busy: false,

      // ---- currency config ----
      curSymbols: {
        JPY:'¥', USD:'$', EUR:'€', GBP:'£',
        AUD:'A$', NZD:'NZ$', CAD:'C$', CHF:'CHF',
        CNY:'¥', HKD:'HK$', SGD:'S$', TWD:'NT$',
        KRW:'₩', THB:'฿', PHP:'₱', VND:'₫',
        INR:'₹', IDR:'Rp', MYR:'RM', MXN:'MX$',
        BRL:'R$', SEK:'kr', NOK:'kr', DKK:'kr',
        PLN:'zł', CZK:'Kč', HUF:'Ft', ZAR:'R',
        TRY:'₺', AED:'AED', SAR:'SAR', RUB:'₽', ILS:'₪'
      },
      zeroDecimals: ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'],

      // ---- utils ----
      // Normalize any input to "HH:MM" (never returns null: fallback '09:00')
      normalizeTime(v) {
        if (!v) return '09:00';
        const s = String(v).trim();
        const m = s.match(/(\d{2}):(\d{2})/); // first HH:MM anywhere
        if (m) return `${m[1]}:${m[2]}`;
        const d = new Date(s);
        if (!isNaN(d)) {
          const hh = String(d.getHours()).padStart(2, '0');
          const mm = String(d.getMinutes()).padStart(2, '0');
          return `${hh}:${mm}`;
        }
        return '09:00';
      },

      // For summary display (always HH:MM)
      displayStart() { return this.normalizeTime(this.start_time); },
      displayEnd()   { return this.normalizeTime(this.end_time); },

      // ---- recalc via quote API ----
      async recalc() {
        if (!this.type || !this.date || !this.start_time || !this.end_time) return;
        this.busy = true;
        try {
          const payload = {
            space_id: this.spaceId,
            type: this.type,
            date: this.date,
            time_from: this.normalizeTime(this.start_time),
            time_to: this.normalizeTime(this.end_time),
            facilities: this.facilities,
            adults: this.adults,
            country_code: this.country,
          };
          const res = await fetch(this.quoteUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': this.csrf,
              'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
          });
          if (!res.ok) throw new Error('Quote request failed: ' + res.status);
          const data = await res.json();

          // Reflect server-decided currency & amounts
          this.total    = Number(data.total || 0);
          this.tax      = Number(data.tax_amount || 0);
          this.currency = String(data.currency || this.currency || 'JPY').toUpperCase();
        } catch (e) {
          console.error('quote error', e);
        } finally {
          this.busy = false;
        }
      },

      // ---- formatters ----
      formatMoney(v, curr) {
        const c = String(curr || 'JPY').toUpperCase();
        const n = Number(v || 0);
        const isZero = this.zeroDecimals.includes(c);
        const symbol = this.curSymbols[c] ?? c;
        if (isZero) {
          return symbol + n.toLocaleString(undefined, { maximumFractionDigits: 0 });
        } else {
          return symbol + n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
      },

      formatDate(iso) {
        if (!iso) return '-';
        const d = new Date(iso + 'T00:00:00');
        if (isNaN(d)) return iso;
        return `${d.getFullYear()}/${String(d.getMonth()+1).padStart(2,'0')}/${String(d.getDate()).padStart(2,'0')}`;
      },
    }
  }
</script>
@endsection
