@extends('layouts.app')

@section('title', 'Register')

@section('content')

<div class="container">
    <div class="mx-auto my-5 p-4 bg-white rounded" style="max-width: 500px;">
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="row mb-3">
                <label for="name" class="form-label fw-bold">Name</label>
                <input type="text" id="name" class="form-control w-100  @error('name') is-invalid @enderror" name="name" placeholder="Enter your first name, last name" required>
                                
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
            </div>

            <div class="row mb-3">
                <label for="email" class="form-label fw-bold">Email Address</label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" placeholder="Enter your email address" required autocomplete="email">

                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
            </div>

            <div class="row mb-3">
                <label for="email-confirm" class="form-label fw-bold">Confirm Email Address</label>
                <input id="email-confirm" type="email" class="form-control @error('email') is-invalid @enderror" name="email_confirmation" placeholder="Confirm your email address" required autocomplete="email">
            </div>

            <div class="row mb-3">
                <label for="password" class="form-label fw-bold">Password</label>
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Enter your password" required autocomplete="new-password">

                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="row mb-3">
                <label for="password-confirm" class="form-label fw-bold">Confirm Password</label>
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="Confirm your password" required autocomplete="new-password">
            </div>

            <div class="row mb-0">
                <button type="submit" class="w-100 btn-register">Register Account</button>
            </div>
        </form>
    </div>
</div>
@endsection