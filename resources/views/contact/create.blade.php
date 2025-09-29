@extends('layout.app')
@section('title','Contact')

@section('content')
<div class="min-h-screen bg-gray-50">
  {{-- Navbar --}}
  <header class="bg-gray-200/80 border-b">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <img src="{{ asset('images/gachi-focus-logo.png') }}" alt="Gachi Focus" class="h-10">
        <span class="sr-only">Gachi Focus</span>
      </div>
      <nav class="flex items-center gap-10 text-lg">
        <a href="{{ route('reservations.index', false) }}" class="hover:text-gray-600">Current Reservation</a>
        <a href="{{ route('reservations.past', false) }}" class="hover:text-gray-600">Past Reservation</a>
        <a href="{{ route('contact.create') }}" class="font-semibold">Contact</a>
      </nav>
      <div class="flex items-center gap-5">
        {{-- bell --}}
        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M14.857 17.082A23.848 23.848 0 0112 17.25c-2.003 0-3.955-.216-5.857-.632m12.714 0A23.848 23.848 0 0012 17.25m7.5-5.25a7.5 7.5 0 10-15 0c0 3.318-.879 5.248-1.5 6h18c-.621-.752-1.5-2.682-1.5-6zM8.25 19.5a3.75 3.75 0 007.5 0"/>
        </svg>
        {{-- avatar + name --}}
        <div class="flex items-center gap-2">
          <div class="w-10 h-10 rounded-full border grid place-content-center">👤</div>
          <span>John Doe</span>
        </div>
      </div>
    </div>
  </header>

  {{-- Form area --}}
  <div class="max-w-3xl mx-auto px-4 py-12">
    @if(session('success'))
      <div class="mb-6 rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3">
        {{ session('success') }}
      </div>
    @endif

    <h1 class="text-2xl font-semibold mb-4">Name</h1>
    <form method="POST" action="{{ route('contact.store') }}" class="space-y-6">
      @csrf

      <input type="text" name="name" value="{{ old('name') }}"
             class="w-full rounded-md border-gray-300 focus:ring-0 focus:border-gray-400"
             placeholder="" />

      <div>
        <label class="block text-xl font-semibold mb-2">E-mail</label>
        <input type="email" name="email" value="{{ old('email') }}"
               class="w-full rounded-md border-gray-300 focus:ring-0 focus:border-gray-400"/>
      </div>

      <div>
        <label class="block text-xl font-semibold mb-2">Phone number</label>
        <input type="text" name="phone" value="{{ old('phone') }}"
               class="w-full rounded-md border-gray-300 focus:ring-0 focus:border-gray-400"/>
      </div>

      <div>
        <label class="block text-xl font-semibold mb-2">Message</label>
        <textarea name="message" rows="8"
          class="w-full rounded-md border-gray-300 focus:ring-0 focus:border-gray-400"
          placeholder="To contact the administrator , please fill out this form.">{{ old('message') }}</textarea>
      </div>

      {{-- errors --}}
      @if($errors->any())
        <div class="text-red-600 text-sm space-y-1">
          @foreach($errors->all() as $e)
            <div>• {{ $e }}</div>
          @endforeach
        </div>
      @endif

      <button type="submit"
        class="w-full rounded-md py-4 text-2xl font-bold text-white bg-indigo-300 hover:bg-indigo-400">
        Send
      </button>
    </form>
  </div>
</div>
@endsection
