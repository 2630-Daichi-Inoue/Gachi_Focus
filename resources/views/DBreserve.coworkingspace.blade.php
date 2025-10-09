@extends('layouts.app')

@section('title', $room['name'].' | GachiFocus')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
{{-- picture --}}
  <div class="relative">
    <img src="{{ $room['image'] }}" alt="{{ $room['name'] }}" class="rounded-xl w-full h-[520px] object-cover">
    <div class="absolute top-4 left-4 text-white/95 drop-shadow-lg">
      <div class="text-5xl font-extrabold">{{ $room['name'] }}</div>
    </div>
  </div>

{{-- form --}}
  <div class="bg-white rounded-xl shadow p-6">
    <form method="POST" action="{{ route('rooms.reserve', $slug) }}" class="space-y-5">
      @csrf

      <div>
        <label class="block text-sm font-medium mb-1">Type</label>
        <select name="type" class="w-full rounded-lg border-gray-300" required>
          <option value="" disabled selected>Select type</option>
          @foreach($types as $t)
            <option value="{{ $t }}">{{ $t }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Date</label>
        <input type="date" name="date" class="w-full rounded-lg border-gray-300" required>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">From</label>
          <input type="time" name="time_from" class="w-full rounded-lg border-gray-300" required>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">To</label>
          <input type="time" name="time_to" class="w-full rounded-lg border-gray-300" required>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Adults</label>
        <select name="adults" class="w-full rounded-lg border-gray-300" required>
          @for($i=1;$i<=$maxAdults;$i++)
            <option value="{{ $i }}">{{ $i }}</option>
          @endfor
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Facilities</label>
        <select name="facilities[]" class="w-full rounded-lg border-gray-300" multiple>
          @foreach($facilities as $f)
            <option value="{{ $f }}">{{ $f }}</option>
          @endforeach
        </select>
      </div>

      <button type="submit"
              class="w-full py-4 rounded-xl bg-slate-500 text-white text-xl font-semibold hover:bg-slate-600">
        Reserve
      </button>
    </form>
  </div>
</div>
@endsection