@extends('layouts.app')

@section('content')
<div class="container bg-white">
    <div class="mx-auto my-5 p-4 rounded" style="max-width: 400px;">

        <div class="d-flex justify-content-center align-items-center">
            <img src="{{ asset('images/GachiFocus_logo.png') }}" alt="" height="100">
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="row mb-3">
                <label for="name" class="form-label">Name</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                    <input type="text" id="name" class="form-control  @error('name') is-invalid @enderror" name="name"  placeholder="Enter Your Name" value="{{ old('email') }}" required autocomplete="email" autofocus>
                </div>

                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="row mb-3">
                <label for="password" class="form-label">{{ __('Password') }}</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Enter Password" required autocomplete="current-password">
                </div>
                
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="row mt-5 mb-3">
                <button type="submit" class="btn-register w-100 mb-3">
                    {{ __('Login') }}
                </button>

                @if (Route::has('password.request'))
                    <a class="btn btn-link" href="{{ route('password.request') }}">
                        {{ __('Forgot Your Password?') }}
                    </a>
                @endif
            </div>
        </form>

        <div class="row">
            <button class="btn radius-3 border-dark"><a href="{{ route('register') }}" class="text-decoration-none text-dark">Register now!</a></button>
        </div>
    </div>
</div>
@endsection
