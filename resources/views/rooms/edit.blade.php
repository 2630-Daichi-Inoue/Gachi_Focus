
@extends('layout.app')

@section('content')
<body class="relative min-h-screen">

  {{-- background temp --}}
  <div class="absolute inset-0 bg-cover bg-center opacity-20 pointer-events-none"
       style="background-image: url('{{ asset('images/room-temp.jpg') }}')"></div>

  {{-- contents --}}
  <div class="relative z-10 max-w-6xl mx-auto ">
    <div class="grid grid-cols-1 lg:grid-cols-2 items-stretch">

      {{-- left column：Room picture --}}
      <div class="relative overflow-hidden shadow-lg">
        <img src="{{ asset('images/room-b.jpg') }}" alt="{{ $room->name }}" class="w-full h-full object-cover">
        <div class="absolute top-4 left-4 text-white/95 drop-shadow-lg">
          <div class="text-5xl font-extrabold">{{ $room->name }}</div>
        </div>
      </div>

      {{-- right column：form --}}
      <div class="bg-white/90 shadow-xl p-8 flex flex-col">
        <form method="POST" action="{{ route('reservations.update', $reservation) }}"
              class="w-full max-w-2xl space-y-8">
          @csrf
          @method('PUT')

          {{-- Section title --}}
          <h2 class="text-3xl lg:text-4xl font-semibold text-gray-900">
            Edit {{ $room->name }} Reservation
          </h2>

          {{-- Type --}}
          <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Type</label>
            <select name="type"
                    class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-gray-900 focus:ring-0 text-gray-900 py-2"
                    required>
              <option value="" disabled {{ old('type', $currentTypeLabel) ? '' : 'selected' }}>Select type</option>
              @foreach($room->types as $t)
                <option value="{{ $t }}" {{ old('type', $currentTypeLabel) === $t ? 'selected' : '' }}>
                  {{ $t }}
                </option>
              @endforeach
            </select>
            @error('type') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- Date --}}
          <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Date</label>
            <input type="date" name="date"
                   value="{{ old('date', \Illuminate\Support\Carbon::parse($reservation->date)->toDateString()) }}"
                   class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-gray-900 focus:ring-0 text-gray-900 py-2"
                   required>
            @error('date') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- Time (From / To) --}}
            @php
            // open time
            $open  = '09:00';
            $close = '21:00';
            $slot  = 30; // minutes

            $startOld = old('start_time', \Illuminate\Support\Carbon::parse($reservation->start_time ?? '09:00')->format('H:i'));
            $endOld   = old('end_time',   \Illuminate\Support\Carbon::parse($reservation->end_time   ?? '09:30')->format('H:i'));

            // From/open To /close
            $fromPeriod = \Carbon\CarbonPeriod::create(
                \Carbon\Carbon::parse($open),
                $slot.' minutes',
                \Carbon\Carbon::parse($close)->subMinutes($slot)
            );
            $toPeriod = \Carbon\CarbonPeriod::create(
                \Carbon\Carbon::parse($open)->addMinutes($slot),
                $slot.' minutes',
                \Carbon\Carbon::parse($close)
            );
            @endphp

            <div class="grid grid-cols-2 gap-8">
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">
                Time (From)
                </label>
                <select name="start_time"
                        class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-gray-900 focus:ring-0 text-gray-900 py-2"
                        required>
                <option value="" disabled {{ $startOld ? '' : 'selected' }}>Select time</option>
                @foreach($fromPeriod as $t)
                    @php $val = $t->format('H:i'); @endphp
                    <option value="{{ $val }}" {{ $startOld === $val ? 'selected' : '' }}>
                    {{ $val }}
                    </option>
                @endforeach
                </select>
                @error('start_time') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">
                Time (To)
                </label>
                <select name="end_time"
                        class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-gray-900 focus:ring-0 text-gray-900 py-2"
                        required>
                <option value="" disabled {{ $endOld ? '' : 'selected' }}>Select time</option>
                @foreach($toPeriod as $t)
                    @php $val = $t->format('H:i'); @endphp
                    <option value="{{ $val }}" {{ $endOld === $val ? 'selected' : '' }}>
                    {{ $val }}
                    </option>
                @endforeach
                </select>
                @error('end_time') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            </div>


          {{-- Adults --}}
          <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Adults</label>
            <select name="adults"
                    class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-gray-900 focus:ring-0 text-gray-900 py-2"
                    required>
              @for($i=1; $i <= $room->max_adults; $i++)
                <option value="{{ $i }}" {{ (int)old('adults', $reservation->adults) === $i ? 'selected' : '' }}>
                  {{ $i }}
                </option>
              @endfor
            </select>
            @error('adults') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- Facilities (checkboxes) --}}
          <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-3">Facilities</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6">
              @php
                $checked = old('facilities', $reservation->facilities ?? []);
                $checked = is_array($checked) ? $checked : [];
              @endphp
              @foreach($room->facilities as $f)
                <label class="flex items-center gap-3 text-gray-900">
                  <input type="checkbox"
                         name="facilities[]"
                         value="{{ $f }}"
                         {{ in_array($f, $checked, true) ? 'checked' : '' }}
                         class="h-4 w-4 rounded-sm border-gray-400 text-neutral-900 focus:ring-neutral-900">
                  <span>{{ $f }}</span>
                </label>
              @endforeach
            </div>
            @error('facilities') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
          </div>

          {{-- Submit button --}}
          <div class="pt-2">
            <button type="submit"
                    class="w-full h-12 rounded-md bg-neutral-900 text-white text-lg font-semibold tracking-wide hover:bg-black transition-colors">
              Save Changes
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</body>
@endsection
