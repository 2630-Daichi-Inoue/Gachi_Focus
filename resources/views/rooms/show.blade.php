@extends('layout.app')

@section('content')

<div class="min-h-screen flex items-center justify-center" x-data="{ showCancel:false }">
    <div class="m-auto grid md:grid-cols-2">

        {{-- left --}}
        <div>
            <img src="{{ asset('images/room-b.jpg') }}" class="shadow w-full h-full object-cover" alt="Room photo">
        </div>

        {{-- right --}}
        <div class="bg-white shadow p-6 rounded-lg flex flex-col justify-between">
            <div class="space-y-4 px-10 py-10">
                <h2 class="text-2xl font-serif">
                    Type
                    {{-- {{ $reservation->type }} --}}
                </h2>

                <dl class="grid grid-cols-3 gap-y-3 text-sm">
                    <dt class="text-gray-500">Date</dt>
                    <dd class="col-span-2">
                        {{ \Carbon\Carbon::parse($reservation->date)->format('M j') }}
                        {{ $reservation->start_time }} - {{ $reservation->end_time }}
                    </dd>

                    <dt class="text-gray-500">Adults</dt>
                    <dd class="col-span-2">{{ $reservation->adults }}</dd>

                    <dt class="text-gray-500">Facilities</dt>
                    <dd class="col-span-2">
                        @forelse(($reservation->facilities ?? []) as $f)
                            <span class="px-2 py-0.5 rounded bg-gray-100">
                                {{ config('reservations.facilities')[$f]['label'] ?? $f }}
                            </span>
                        @empty
                            <span class="text-gray-400">None</span>
                        @endforelse
                    </dd>

                    @php
                        // use total_price if exists, else quote_total
                        $total = (int)($reservation->total_price ?? $reservation->quote_total ?? 0);
                    @endphp

                    <dt class="text-gray-500">Total</dt>
                    <dd class="col-span-2 font-semibold">
                        ¥{{ number_format($total) }}
                    </dd>

                    <dt class="text-gray-500">Payment</dt>
                    <dd class="col-span-2">
                        @if(($reservation->payment_status ?? 'unpaid') === 'paid')
                            <span class="inline-flex items-center rounded-full bg-emerald-50 text-emerald-700 px-3 py-1 text-sm">Paid</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-amber-50 text-amber-700 px-3 py-1 text-sm">Unpaid</span>
                        @endif
                    </dd>
                </dl>
            </div>

            {{-- actions --}}
            <div class="space-y-2 mt-6 text-center">

                {{-- show pay button only when unpaid and amount > 0 --}}
                @if(($reservation->payment_status ?? 'unpaid') !== 'paid' && $total > 0)
                    <script src="https://js.stripe.com/v3/"></script>

                    {{-- primary: pay --}}
                    <button id="pay-btn"
                        type="button"
                        class="inline-flex w-full justify-center rounded-lg bg-indigo-600 text-white py-3 font-semibold hover:bg-indigo-700">
                        Pay with card
                    </button>
                @endif

                {{-- secondary: change --}}
                <a href="{{ route('reservations.edit', $reservation) }}"
                   class="inline-flex w-full justify-center rounded-lg bg-neutral-900 text-white py-3 font-semibold">
                   Change reservation
                </a>

                {{-- tertiary: cancel (open modal) --}}
                <button type="button"
                        class="inline-flex w-full justify-center rounded-lg border-2 border-red-500 py-3 font-semibold text-red-600 hover:bg-red-50"
                        x-on:click="showCancel=true">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    {{-- cancel modal --}}
    <div x-show="showCancel" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/40" x-on:click="showCancel=false"></div>

        <div class="relative w-[92%] max-w-md rounded-xl bg-white shadow-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Are you sure?</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" aria-label="Close"
                        x-on:click="showCancel=false">&times;</button>
            </div>

            <p class="mb-6 text-gray-700">Are you sure you want to cancel this reservation?</p>

            <div class="flex gap-3">
                <form method="POST" action="{{ route('reservations.destroy', $reservation) }}" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full rounded-lg bg-red-600 px-4 py-2.5 font-semibold text-white hover:bg-red-700">
                        Cancel
                    </button>
                </form>

                <button type="button"
                        class="flex-1 rounded-lg border px-4 py-2.5 font-semibold text-gray-700 hover:bg-gray-50"
                        x-on:click="showCancel=false">
                    Not now
                </button>
            </div>
        </div>
    </div>
</div>

{{-- pay script (simple & safe) --}}
@if(($reservation->payment_status ?? 'unpaid') !== 'paid' && $total > 0)
<script>
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('pay-btn');
  if (!btn) return;

  let busy = false; // prevent double click

  btn.addEventListener('click', async () => {
    if (busy) return;
    busy = true;
    btn.disabled = true;
    btn.textContent = 'Processing...';

    try {
      const stripe = Stripe(@json(config('services.stripe.public'))); // public key
      const res = await fetch(@json(route('payments.checkout')), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': @json(csrf_token()),
          'Accept': 'application/json'
        },
        body: JSON.stringify({ reservation_id: @json($reservation->id) }) // required id
      });

      if (!res.ok) {
        const t = await res.text();
        alert('Failed to start payment.\n' + t);
        btn.disabled = false; btn.textContent = 'Pay with card'; busy = false;
        return;
      }

      const { id } = await res.json(); // checkout session id
      const { error } = await stripe.redirectToCheckout({ sessionId: id });
      if (error) {
        alert(error.message || 'Stripe error');
        btn.disabled = false; btn.textContent = 'Pay with card'; busy = false;
      }
    } catch (e) {
      alert((e && e.message) ? e.message : 'Unexpected error');
      btn.disabled = false; btn.textContent = 'Pay with card'; busy = false;
    }
  });
});
</script>
@endif

@endsection
