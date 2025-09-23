{{-- resources/views/reservations/show.blade.php --}}
@extends('layout.app')

@section('content')
<div class="max-w-6xl mx-auto grid md:grid-cols-2 gap-8 py-8">
  <div>
    <img src="/img/room-b.jpg" class="rounded-xl shadow">
  </div>

  <div class="bg-white rounded-xl shadow p-6 space-y-4">
    <h2 class="text-2xl font-serif">Type 
        {{-- {{ $reservation->type }} --}}
    </h2>

    <dl class="grid grid-cols-3 gap-y-3 text-sm">
      <dt class="text-gray-500">Date</dt>
      <dd class="col-span-2">{{ \Carbon\Carbon::parse($reservation->date)->format('M j') }}
        {{ $reservation->start_time }} - {{ $reservation->end_time }}</dd>

      <dt class="text-gray-500">Adults</dt>
      <dd class="col-span-2">{{ $reservation->adults }}</dd>

      <dt class="text-gray-500">Facilities</dt>
      <dd class="col-span-2">
        @forelse(($reservation->facilities ?? []) as $f)
          <span class="px-2 py-0.5 rounded bg-gray-100">{{ config('reservations.facilities')[$f]['label'] ?? $f }}</span>
        @empty <span class="text-gray-400">None</span>
        @endforelse
      </dd>

      <dt class="text-gray-500">Total</dt>
      <dd class="col-span-2 font-semibold">${{ number_format($reservation->total_price, 2) }}</dd>
    </dl>

    <a href="{{ route('reservations.edit', $reservation) }}"
       class="inline-flex w-full justify-center rounded-lg bg-indigo-600 text-white py-3 font-semibold">
      Change reservation
    </a>

    <p class="text-[11px] text-center text-gray-400">You want to cancel this reservation?</p>
  </div>
</div>
@endsection
