@extends('layout.app')

@section('title', 'Edit '.$room->name.' | GachiFocus')

@section('content')
{{-- Alpine for live pricing --}}
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

@php
  // If controller didn't provide time arrays, build fallback here (09:00-21:00 / 30min slot)
  if (!isset($fromTimes) || !isset($toTimes)) {
      $open  = '09:00';
      $close = '21:00';
      $slot  = 30;
      $fromTimes = [];
      $toTimes   = [];
      $from = \Carbon\Carbon::createFromTimeString($open);
      $to   = \Carbon\Carbon::createFromTimeString($close);
      for ($t = $from->copy(); $t->lt($to->copy()->subMinutes($slot)); $t->addMinutes($slot)) {
          $fromTimes[] = $t->format('H:i');
      }
      for ($t = $from->copy()->addMinutes($slot); $t->lte($to); $t->addMinutes($slot)) {
          $toTimes[] = $t->format('H:i');
      }
  }

  // Defaults for form/x-data (prefer old() then reservation)
  $defaultTypeLabel = old('type', $currentTypeLabel ?? null);
  $defaultDate      = old('date', \Illuminate\Support\Carbon::parse($reservation->date)->toDateString());
  $defaultStart     = old('start_time', \Illuminate\Support\Carbon::parse($reservation->start_time)->format('H:i'));
  $defaultEnd       = old('end_time',   \Illuminate\Support\Carbon::parse($reservation->end_time)->format('H:i'));
  $defaultAdults    = (int) old('adults', (int)$reservation->adults);
  $defaultFacilities= old('facilities', $reservation->facilities ?? []);
  if (!is_array($defaultFacilities)) $defaultFacilities = [];
@endphp

<body class="relative min-h-screen"
      x-data="editPage({
        csrf: '{{ csrf_token() }}',
        quoteUrl: '{{ route('pricing.quote') }}',
        roomName: '{{ $room->name }}',
        type: @js($defaultTypeLabel),
        date: @js($defaultDate),
        start_time: @js($defaultStart),
        end_time: @js($defaultEnd),
        adults: @js($defaultAdults),
        facilities: @js($defaultFacilities),
      })"
      x-init="recalc()">

  {{-- background --}}
  <div class="absolute inset-0 bg-cover bg-center opacity-20 pointer-events-none"
       style="background-image: url('{{ asset('images/room-temp.jpg') }}')"></div>

  {{-- contents --}}
  <div class="relative z-10 max-w-6xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-2 items-stretch">

      {{-- left: photo --}}
      <div class="relative overflow-hidden shadow-lg">
        <img src="{{ asset('images/room-b.jpg') }}" alt="{{ $room->name }}" class="w-full h-full object-cover">
        <div class="absolute top-4 left-4 text-white/95 drop-shadow-lg">
          <div class="text-5xl font-extrabold">{{ $room->name }}</div>
        </div>
      </div>

      {{-- right: form + live summary --}}
      <div class="bg-white/90 shadow-xl p-8 flex flex-col">
        <form method="POST" action="{{ route('reservations.update', $reservation) }}"
              class="w-full max-w-2xl space-y-8">
          @csrf
          @method('PUT')

          <h2 class="text-3xl lg:text-4xl font-semibold text-gray-900">
            Edit {{ $room->name }} Reservation
          </h2>

          {{-- TYPE (label-based) --}}
          <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Type</label>
            <select name="type"
                    x-model="type" x-on:change="recalc()"
                    class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-gray-900 focus:ring-0 text-gray-900 py-2"
                    required>
              <option value="" disabled {{ $defaultTypeLabel ? '' : 'selected' }}>Select type</option>
              @foreach($room->types as $t)
                <option value="{{ $t }}">{{ $t }}</option>
              @endforeach
            </select>
            @error('type') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- DATE --}}
          <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Date</label>
            <input type="date" name="date"
                   x-model="date" x-on:change="recalc()"
                   class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-gray-900 focus:ring-0 text-gray-900 py-2"
                   required>
            @error('date') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- TIME (we submit start_time/end_time, but quote() needs time_from/time_to → JS側で変換して送る) --}}
          <div class="grid grid-cols-2 gap-8">
            <div>
              <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Time (From)</label>
              <select name="start_time"
                      x-model="start_time" x-on:change="recalc()"
                      class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-gray-900 focus:ring-0 text-gray-900 py-2"
                      required>
                <option value="" disabled {{ $defaultStart ? '' : 'selected' }}>Select time</option>
                @foreach($fromTimes as $val)
                  <option value="{{ $val }}">{{ $val }}</option>
                @endforeach
              </select>
              @error('start_time') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Time (To)</label>
              <select name="end_time"
                      x-model="end_time" x-on:change="recalc()"
                      class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-gray-900 focus:ring-0 text-gray-900 py-2"
                      required>
                <option value="" disabled {{ $defaultEnd ? '' : 'selected' }}>Select time</option>
                @foreach($toTimes as $val)
                  <option value="{{ $val }}">{{ $val }}</option>
                @endforeach
              </select>
              @error('end_time') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
          </div>

          {{-- ADULTS --}}
          <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Adults</label>
            <select name="adults"
                    x-model.number="adults" x-on:change="recalc()"
                    class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-gray-900 focus:ring-0 text-gray-900 py-2"
                    required>
              @for($i=1; $i <= $room->max_adults; $i++)
                <option value="{{ $i }}">{{ $i }}</option>
              @endfor
            </select>
            @error('adults') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- FACILITIES --}}
          <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-3">Facilities</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6">
              @foreach($room->facilities as $f)
                <label class="flex items-center gap-3 text-gray-900">
                  <input type="checkbox"
                         name="facilities[]"
                         value="{{ $f }}"
                         x-model="facilities" x-on:change="recalc()"
                         @checked(in_array($f, $defaultFacilities, true))
                         class="h-4 w-4 rounded-sm border-gray-400 text-neutral-900 focus:ring-neutral-900">
                  <span>{{ $f }}</span>
                </label>
              @endforeach
            </div>
            @error('facilities') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- (optional) send total to server as hidden, even if server recomputes --}}
          <input type="hidden" name="total_price" :value="total">

          {{-- SUBMIT --}}
          <div class="pt-2">
            <button type="submit"
                    class="w-full h-12 rounded-md bg-neutral-900 text-white text-lg font-semibold tracking-wide hover:bg-black transition-colors">
              Save Changes
            </button>
          </div>
        </form>

        {{-- Live summary (same look & feel as your show page) --}}
        <div class="bg-white shadow mt-8 p-6 rounded-lg">
          <div class="space-y-4 px-4 py-4">
            <h2 class="text-2xl font-serif">
              <span x-text="type || 'Type'"></span>
            </h2>

            <dl class="grid grid-cols-3 gap-y-3 text-sm">
              <dt class="text-gray-500">Date</dt>
              <dd class="col-span-2">
                <span x-text="formatDate(date)"></span>
                <template x-if="start_time && end_time">
                  <span> <span x-text="start_time"></span> - <span x-text="end_time"></span></span>
                </template>
              </dd>

              <dt class="text-gray-500">Adults</dt>
              <dd class="col-span-2" x-text="adults || '-'"></dd>

              <dt class="text-gray-500">Facilities</dt>
              <dd class="col-span-2">
                <template x-if="facilities.length">
                  <div class="flex flex-wrap gap-2">
                    <template x-for="f in facilities" :key="f">
                      <span class="px-2 py-0.5 rounded bg-gray-100" x-text="f"></span>
                    </template>
                  </div>
                </template>
                <template x-if="!facilities.length">
                  <span class="text-gray-400">None</span>
                </template>
              </dd>

              <dt class="text-gray-500">Total</dt>
              <dd class="col-span-2 font-semibold">
                <span x-show="!busy" x-text="formatJPY(total)"></span>
                <span x-show="busy" class="text-gray-400">calculating...</span>
              </dd>
            </dl>
          </div>
        </div>

      </div>
    </div>
  </div>

  {{-- Alpine logic: reuse server-side Pricing::calc() via /pricing/quote --}}
  <script>
    function editPage(init) {
      return {
        csrf: init.csrf,
        quoteUrl: init.quoteUrl,
        roomName: init.roomName,

        // form state
        type: init.type || '',
        date: init.date || '',
        start_time: init.start_time || '',
        end_time: init.end_time || '',
        adults: init.adults || 1,
        facilities: Array.isArray(init.facilities) ? init.facilities : [],

        // result state
        total: 0,
        busy: false,

        async recalc() {
          if (!this.type || !this.date || !this.start_time || !this.end_time) return;
          this.busy = true;
          try {
            const res = await fetch(this.quoteUrl, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrf,
                'Accept': 'application/json',
              },
              // quote() expects time_from/time_to, while form uses start_time/end_time
              body: JSON.stringify({
                type: this.type,
                date: this.date,
                time_from: this.start_time,
                time_to: this.end_time,
                facilities: this.facilities,
                adults: this.adults,
              }),
            });
            if (!res.ok) throw new Error('Quote failed: ' + res.status);
            const data = await res.json();
            this.total = Number(data.total || 0);
          } catch (e) {
            console.error(e);
          } finally {
            this.busy = false;
          }
        },

        formatJPY(v) {
          const n = Number(v || 0);
          return new Intl.NumberFormat('ja-JP', { style: 'currency', currency: 'JPY' }).format(n);
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
</body>
@endsection
