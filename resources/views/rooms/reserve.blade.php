@extends('layout.app')

@section('title', $room->name.' | GachiFocus')

@section('content')
<body class="relative min-h-screen">

  {{-- background temp --}}
  <div class="absolute inset-0 bg-cover bg-center opacity-20"
       style="background-image: url('{{ asset('images/room-temp.jpg') }}')"></div>

  {{-- contents --}}
  <div class="relative z-10 max-w-6xl mx-auto ">
    <div class="grid grid-cols-1 lg:grid-cols-2  items-stretch">

     {{-- left column：Room picture --}}
      <div class="relative  overflow-hidden shadow-lg">
        <img src="{{ asset('images/room-b.jpg') }}" alt="{{ $room->name }}" class="w-full h-full object-cover">
        <div class="absolute top-4 left-4 text-white/95 drop-shadow-lg">
          <div class="text-5xl font-extrabold">{{ $room->name }}</div>
        </div>
      </div>

      {{-- roght column：form --}}
      <div class="bg-white/90 shadow-xl p-8 flex flex-col">
        <form method="POST" action="{{ route('rooms.reserve.submit') }}"
            class="w-full max-w-2xl space-y-8">
        @csrf

        {{-- Section title: darker gray/near-black --}}
        <h2 class="text-3xl lg:text-4xl font-semibold text-gray-900">Reserve {{ $room->name }}</h2>

        {{-- NOTE: Each control uses "border-b only, transparent bg, no ring" for a clean look --}}
        {{-- Type --}}
        <div>
          {{-- label: small, uppercase, muted --}}
          <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Type</label>
          <select name="type"
                  class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-gray-900 focus:ring-0 text-gray-900 py-2"
                  required>
            <option value="" disabled selected>Select type</option>
            @foreach($room->types as $t)
              <option value="{{ $t }}">{{ $t }}</option>
            @endforeach
          </select>
        </div>

        {{-- Date --}}
        <div>
          <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Date</label>
          <input type="date" name="date"
                class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-gray-900 focus:ring-0 text-gray-900 py-2"
                required>
        </div>

        {{-- Time (From / To) --}}
        <div class="grid grid-cols-2 gap-8">
          <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Time (From)</label>
            <input type="time" name="time_from"
                  class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-gray-900 focus:ring-0 text-gray-900 py-2"
                  required>
          </div>
          <div>
            <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Time (To)</label>
            <input type="time" name="time_to"
                  class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-gray-900 focus:ring-0 text-gray-900 py-2"
                  required>
          </div>
        </div>

        {{-- Adults --}}
        <div>
          <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Adults</label>
          <select name="adults"
                  class="w-full bg-transparent border-0 border-b border-gray-300 focus:border-gray-900 focus:ring-0 text-gray-900 py-2"
                  required>
            @for($i=1;$i<=$room->max_adults;$i++)
              <option value="{{ $i }}">{{ $i }}</option>
            @endfor
          </select>
        </div>

        {{-- Facilities (checkboxes with dark accent) --}}
        <div>
          <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-3">Facilities</label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-6">
            @foreach($room->facilities as $f)
              <label class="flex items-center gap-3 text-gray-900">
                {{-- Use darker accent for a black/gray theme --}}
                <input type="checkbox"
                      name="facilities[]"
                      value="{{ $f }}"
                      @if(in_array($f, old('facilities', []))) checked @endif
                      class="h-4 w-4 rounded-sm border-gray-400 text-neutral-900 focus:ring-neutral-900">
                <span>{{ $f }}</span>
              </label>
            @endforeach
          </div>
        </div>

        {{-- Submit button--}}
        <div class="pt-2">
          <button type="submit"
                  class="w-full h-12 rounded-md bg-neutral-900 text-white text-lg font-semibold tracking-wide hover:bg-black transition-colors">
            Reserve
          </button>
        </div>

      </form>
    </div>

    </div>
  </div>
</body>
@endsection