@extends('layouts.app')

@section('title', 'Contact')

@section('content')
  <div class="mx-auto col-md-8 col-lg-6 bg-white shadow-sm rounded-4 p-5 mt-3">
    <h1 class="h4 fw-semibold mb-4">Contact</h1>

    <form method="POST" action="{{ route('contact.store') }}">
      @csrf

      <div class="mb-3">
        <label class="form-label fw-medium fw-semibold">Name</label>
        <input type="text" name="name" value="{{ old('name') }}" class="form-control"required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-medium fw-semibold">E-mail</label>
        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-medium fw-semibold">Phone number</label>
        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
      </div>

      <div class="mb-3">
        <label class="form-label fw-medium fw-semibold">Message</label>
        <textarea name="message" rows="6"
          class="form-control" placeholder="To contact the administrator , please fill out this form."
          required>{{ old('message') }}</textarea>
      </div>

      {{-- show error --}}
      @if($errors->any())
        <div class="alert alert-danger py-2">
          <ul class="mb-0">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="d-grid mt-4">
        <button type="submit"class="btn btn-color py-2 text-white fw-bold">
          Send
        </button>
      </div>
    </form>
  </div>
@endsection
