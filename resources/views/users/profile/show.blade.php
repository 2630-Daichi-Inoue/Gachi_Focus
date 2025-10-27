@extends('layouts.app')

@section('title', 'Profile')

@section('content')


<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-8">
            <div class="card card-body bg-white border-0">
                <div class="row align-items-start">

                    <!-- image -->
                    <div class="col-4 text-center">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="img-fluid rounded-cirle image-lg">
                        @else
                            <i class="fas fa-circle-user text-secondary icon-lg"></i>
                        @endif
                    </div>

                    <!-- profile information -->
                    <div class="col-8 mb-5">
                        <h5 class="fw-bold">Name</h5>
                        <h6 class="border rounded border-dark ps-2 pt-2 pb-2 mb-3">{{ $user->name }}</h6>
                        <h5 class="fw-bold">Email Address</h5>
                        <h6 class="border rounded border-dark ps-2 pt-2 pb-2 mb-3">{{ $user->email }}</h6>
                        <h5 class="fw-bold">Phone Number (optional)</h5>
                        <h6 class="border rounded border-dark ps-2 pt-2 pb-2 mb-3">{{ $user->phone ?? '-' }}</h6>
                        <h5 class="fw-bold">Country (optional)</h5>
                        <h6 class="border rounded border-dark ps-2 pt-2 pb-2 mb-3">{{ $user->country ?? '-' }}</h6>

                        <!-- Button -->
                        <div class="row mt-5 g-2">
                            <div class="col-6">
                                <button class="w-100 h-100 bg-white text-danger border border-danger fw-bold rounded" data-bs-toggle="modal" data-bs-target="#delete-user-{{ $user->id }}">Delete Account</button>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('profile.edit') }}" class="btn w-100 btn-success text-white fw-bold rounded">Edit Profile</a>
                            </div>

                            <!-- delete modal -->
                            @include('users.profile.modals.delete')
                        </div>
                    </div>
                </div>
            </div>           
        </div>
    </div>
</div>

@endsection