@extends('layout.app')

@section('title', $room->name.' | GachiFocus')

@section('content')
{{-- Alpine for live pricing --}}
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<body class="relative min-h-screen"
      x-data="reservePage('{{ csrf_token() }}', '{{ route('pricing.quote') }}', '{{ $room->name }}')">

  {{-- background --}}
  <div class="absolute inset-0 bg-cover bg-center opacity-20"
       style="background-image: url('{{ asset('images/room-temp.jpg') }}')"></div>

  {{-- contents --}}
  <div class="relative z-10 max-w-6xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-2 items-stretch gap-0">

      {{-- left: photo --}}
      <div class="relative overflow-hidden shadow-lg">
        <img src="{{ asset('images/room-b.jpg') }}" alt="{{ $room->name }}" class="w-full h-full object-cover">
        <div class="absolute top-4 left-4 text-white/95 drop-shadow-lg">
          <div class="text-5xl font-extrabold">{{ $room->name }}</div>
        </div>
      </div>

      {{-- right: form + summary --}}
      <div class="bg-white/90 shadow-xl p-8 flex flex-col">
        <form method="POST"
              action="{{ route('rooms.reserve.submit') }}"
              class="w-full max-w-2xl space-y-8"
              x-on:submit="injectTotal()">
          @csrf

          <h2 class="text-3xl lg:text-4xl font-semibold text-gray-900">
            Reserve {{ $room->name }}
          </h2>

          {{-- TYPE --}}
          <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Type</label>
            <select name="type"
                    x-model="type" x-on:change="recalc()"
                    class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-gray-900 focus:ring-0 text-gray-900 py-2"
                    required>
              <option value="" disabled selected>Select type</option>
              @foreach($room->types as $t)
                <option value="{{ $t }}">{{ $t }}</option>
              @endforeach
            </select>
          </div>

          {{-- DATE --}}
          <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Date</label>
            <input type="date" name="date"
                   x-model="date" x-on:change="recalc()"
                   class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-gray-900 focus:ring-0 text-gray-900 py-2"
                   required>
          </div>

          {{-- TIME --}}
          <div class="grid grid-cols-2 gap-8">
            <div>
              <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Time (From)</label>
              <select name="time_from"
                      x-model="time_from" x-on:change="recalc()"
                      class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-gray-900 focus:ring-0 text-gray-900 py-2"
                      required>
                <option value="" disabled selected>Select time</option>
                @foreach($fromTimes as $val)
                  <option value="{{ $val }}">{{ $val }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Time (To)</label>
              <select name="time_to"
                      x-model="time_to" x-on:change="recalc()"
                      class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-gray-900 focus:ring-0 text-gray-900 py-2"
                      required>
                <option value="" disabled selected>Select time</option>
                @foreach($toTimes as $val)
                  <option value="{{ $val }}">{{ $val }}</option>
                @endforeach
              </select>
            </div>
          </div>

          {{-- ADULTS --}}
          <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Adults</label>
            <select name="adults"
                    x-model.number="adults" x-on:change="recalc()"
                    class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-gray-900 focus:ring-0 text-gray-900 py-2"
                    required>
              @for($i = 1; $i <= $room->max_adults; $i++)
                <option value="{{ $i }}">{{ $i }}</option>
              @endfor
            </select>
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
                         class="h-4 w-4 rounded-sm border-gray-400 text-neutral-900 focus:ring-neutral-900">
                  <span>{{ $f }}</span>
                </label>
              @endforeach
            </div>
          </div>

          {{-- hidden: total to submit --}}
          <input type="hidden" name="total_price" :value="total">

          {{-- ACTIONS --}}
          <div class="pt-2 space-y-3">
            <button type="submit"
                    class="w-full h-12 rounded-md bg-neutral-900 text-white text-lg font-semibold tracking-wide hover:bg-black transition-colors">
              Reserve
            </button>

            {{-- Optional: go to preview/payment using the same form values --}}
            <button type="submit"
                    formaction="{{ route('rooms.reserve.preview') }}"
                    class="w-full h-12 rounded-md border-2 border-neutral-900 text-neutral-900 text-lg font-semibold tracking-wide hover:bg-gray-50 transition-colors">
              Proceed to payment
            </button>
          </div>
        </form>

        {{-- SUMMARY (live, same style as your show/preview) --}}
        <div class="bg-white shadow mt-8 p-6 rounded-lg">
          <div class="space-y-4 px-4 py-4">
            <h2 class="text-2xl font-serif">
              <span x-text="type || 'Type'"></span>
            </h2>

            <dl class="grid grid-cols-3 gap-y-3 text-sm">
              <dt class="text-gray-500">Date</dt>
              <dd class="col-span-2">
                <span x-text="formatDate(date)"></span>
                <template x-if="time_from && time_to">
                  <span>
                    <span x-text="time_from"></span> - <span x-text="time_to"></span>
                  </span>
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

  {{-- Alpine component: calls /pricing/quote and shows total --}}
  <script>
    function reservePage(csrf, quoteUrl, roomName) {
      return {
        // form state
        type: '',
        date: '',
        time_from: '',
        time_to: '',
        adults: 1,
        facilities: [],

        // result
        total: 0,
        busy: false,

        async recalc() {
          // wait until the minimum required fields are filled
          if (!this.type || !this.date || !this.time_from || !this.time_to) return;

          this.busy = true;
          try {
            const res = await fetch(quoteUrl, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
              },
              body: JSON.stringify({
                type: this.type,
                date: this.date,
                time_from: this.time_from,
                time_to: this.time_to,
                facilities: this.facilities,
                adults: this.adults,   // not used by current Pricing, but fine to send
              }),
            });
            if (!res.ok) throw new Error('Quote request failed: ' + res.status);
            const data = await res.json();
            this.total = Number(data.total || 0);
          } catch (e) {
            console.error(e);
          } finally {
            this.busy = false;
          }
        },

        injectTotal() {
          // Just ensure latest total is written to hidden input before submit
          if (!this.total) this.recalc();
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
