@extends('layout.app')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="m-auto grid md:grid-cols-2">

        {{-- picture --}}
        <div>
            <img src="{{ asset('images/room-b.jpg') }}" class="shadow" class="w-full h-full object-cover">
        </div>

        {{-- content --}}
        <div class="bg-white shadow p-6 rounded-lg flex flex-col justify-between">
            <div class="space-y-4 px-10 py-10">
                <h2 class="text-2xl font-serif">Type 
                    {{-- {{ $reservation->type }} --}}
                </h2>

                <dl class="grid grid-cols-3 gap-y-3 text-sm">
                    <dt class="text-gray-500">Date</dt>
                    <dd class="col-span-2">{{ \Carbon\Carbon::parse($reservation->date)->format('M j') }}
                        {{ $reservation->start_time }} - {{ $reservation->end_time }}
                    </dd>

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
            </div>
            <div class="space-y-2 mt-6 text-center">
                <a href="{{ route('reservations.edit', $reservation) }}"
                class="inline-flex w-full justify-center rounded-lg bg-neutral-900 text-white py-3 font-semibold">
                Change reservation
                </a>

                <a href="#"
                    class="text-[11px] text-center text-gray-400">You want to cancel this reservation?
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
