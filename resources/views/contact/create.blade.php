@extends('layout.app')

@section('title', 'Contact')

@section('content')
  <div class="mx-auto max-w-lg rounded-xl bg-white shadow-xl p-8">
    <h1 class="text-xl font-semibold mb-6">Contact</h1>

    <form method="POST" action="{{ route('contact.store') }}" class="space-y-6">
      @csrf

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
        <input type="text" name="name" value="{{ old('name') }}"
               class="w-full rounded-md border border-gray-300 focus:border-gray-500 focus:ring-0 p-2"
               required>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
        <input type="email" name="email" value="{{ old('email') }}"
               class="w-full rounded-md border border-gray-300 focus:border-gray-500 focus:ring-0 p-2"
               required>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Phone number</label>
        <input type="text" name="phone" value="{{ old('phone') }}"
               class="w-full rounded-md border border-gray-300 focus:border-gray-500 focus:ring-0 p-2">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
        <textarea name="message" rows="6"
          class="w-full rounded-md border border-gray-300 focus:border-gray-500 focus:ring-0 p-2"
          placeholder="To contact the administrator , please fill out this form."
          required>{{ old('message') }}</textarea>
      </div>

      {{-- show error --}}
      @if($errors->any())
        <div class="text-red-600 text-sm space-y-1">
          @foreach($errors->all() as $error)
            <p>• {{ $error }}</p>
          @endforeach
        </div>
      @endif

      <button type="submit"
        class="w-full rounded-md py-3 text-base font-medium text-white bg-gray-600 hover:bg-gray-700 transition">
        Send
      </button>
    </form>
  </div>
@endsection
