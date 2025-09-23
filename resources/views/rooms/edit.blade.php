
@extends('layout.app')

@section('content')
@php
  $types = $types ?? [];
  $fac   = $facilities ?? [];
@endphp

<div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-8 py-8">
  <div>
    <img src="/img/room-b.jpg" class="rounded-xl shadow">
  </div>

  <div
    x-data="reservationForm({
      types: @js($types),
      fac: @js($fac),
      initType: @js($reservation->type),
      initDate: @js($reservation->date),
      initStart: @js($reservation->start_time),
      initEnd: @js($reservation->end_time),
      initAdults: @js($reservation->adults),
      initFacilities: @js($reservation->facilities ?? []),
    })"
    x-init="init()"
    class="bg-white rounded-xl shadow p-6 space-y-5"
  >
    <form method="POST" action="{{ route('reservations.update', $reservation) }}" class="space-y-4">
      @csrf
      @method('PUT')

      {{-- Type --}}
      <label class="block text-sm font-medium">Type</label>
      <select name="type" x-model="type"
              class="w-full rounded border-gray-300">
        @foreach($types as $k => $t)
          <option value="{{ $k }}">Type {{ $k }} ({{ $t['price_per_hour'] }}$/h)</option>
        @endforeach
      </select>
      @error('type') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

      {{-- Date --}}
      <label class="block text-sm font-medium mt-3">Date</label>
      <input type="date" name="date" x-model="date" class="w-full rounded border-gray-300">
      @error('date') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

      {{-- Time --}}
      <div class="grid grid-cols-2 gap-3 mt-3">
        <div>
          <label class="block text-sm font-medium">From</label>
          <select name="start_time" x-model="start" class="w-full rounded border-gray-300">
            @foreach(\Carbon\CarbonPeriod::create($open, $slot.' minutes', \Carbon\Carbon::parse($close)->subMinutes($slot)) as $t)
              <option value="{{ $t->format('H:i') }}">{{ $t->format('H:i') }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium">To</label>
          <select name="end_time" x-model="end" class="w-full rounded border-gray-300">
            @foreach(\Carbon\CarbonPeriod::create(\Carbon\Carbon::parse($open)->addMinutes($slot), $slot.' minutes', $close) as $t)
              <option value="{{ $t->format('H:i') }}">{{ $t->format('H:i') }}</option>
            @endforeach
          </select>
        </div>
      </div>
      @error('start_time') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
      @error('end_time')   <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

      {{-- Adults --}}
      <label class="block text-sm font-medium mt-3">Adults</label>
      <input type="number" min="1" max="20" name="adults" x-model.number="adults"
             class="w-full rounded border-gray-300">
      @error('adults') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

      {{-- Facilities --}}
      <label class="block text-sm font-medium mt-3">Facilities</label>
      <select multiple name="facilities[]"
              x-model="facilities"
              class="w-full rounded border-gray-300 h-24">
        @foreach($facilities as $k => $f)
          <option value="{{ $k }}">{{ $f['label'] }} {{ $f['price'] ? '(+$'.$f['price'].')' : '' }}</option>
        @endforeach
      </select>

      {{-- 合計表示 --}}
      <div class="flex items-center justify-between mt-4 text-lg">
        <span class="font-semibold">Total</span>
        <span class="font-bold" x-text="`$${total.toFixed(2)}`"></span>
      </div>

      <button type="submit"
        class="w-full py-3 rounded-lg bg-indigo-600 text-white font-semibold mt-2">
        Change
      </button>
    </form>
  </div>
</div>

{{-- Alpine --}}
<script>
function reservationForm({types, fac, initType, initDate, initStart, initEnd, initAdults, initFacilities}) {
  return {
    types, fac,
    type: initType, date: initDate, start: initStart, end: initEnd,
    adults: Number(initAdults || 1),
    facilities: initFacilities || [],
    total: 0,
    init(){ this.calc(); this.$watch('type', ()=>this.calc());
            this.$watch('start', ()=>this.calc()); this.$watch('end', ()=>this.calc());
            this.$watch('adults', ()=>this.calc()); this.$watch('facilities', ()=>this.calc()); },
    hours(){
      const [sH,sM] = this.start.split(':').map(Number);
      const [eH,eM] = this.end.split(':').map(Number);
      const h = (eH*60+eM - (sH*60+sM)) / 60;
      return Math.max(0, h);
    },
    calc(){
      const basePerH = this.types[this.type]?.price_per_hour ?? 0;
      const base = basePerH * this.hours();
      const facSum = (this.facilities||[]).reduce((sum,k)=> sum + (this.fac[k]?.price ?? 0), 0);
      this.total = (base + facSum) * Math.max(1, Number(this.adults||1));
    }
  }
}
</script>
@endsection
