@extends('layouts.app')

@section('title', 'Contact')

@section('content')
<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card shadow-sm border-0 rounded-4">
      <div class="card-body p-4 p-md-5">
        <h1 class="h4 mb-4">Contact</h1>

        {{-- success flash --}}
        @if (session('status'))
          <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('contact.store') }}" novalidate>
          @csrf

          {{-- Name --}}
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input
              type="text"
              name="name"
              value="{{ old('name') }}"
              class="form-control @error('name') is-invalid @enderror"
              required
            >
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          {{-- Email --}}
          <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input
              type="email"
              name="email"
              value="{{ old('email') }}"
              class="form-control @error('email') is-invalid @enderror"
              required
            >
            @error('email')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          {{-- Phone --}}
          <div class="mb-3">
            <label class="form-label">Phone number</label>
            <input
              type="text"
              name="phone"
              value="{{ old('phone') }}"
              class="form-control @error('phone') is-invalid @enderror"
            >
            @error('phone')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          {{-- Message --}}
          <div class="mb-4">
            <label class="form-label">Message</label>
            <textarea
              name="message"
              rows="6"
              class="form-control @error('message') is-invalid @enderror"
              placeholder="To contact the administrator, please fill out this form."
              required>{{ old('message') }}</textarea>
            @error('message')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          {{-- error summary (optional) --}}
          @if($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <button type="submit"
            class="btn w-100 py-2 text-white"
            style="background-color:#6c757d; border:none;">
            Send
          </button>

        </form>
      </div>
    </div>
  </div>
</div>
@endsection
