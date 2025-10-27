@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')

<div class="container mt-4">
    <form action="{{ route('profile.update') }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <div class="card card-body border-dark bg-white mb-3">
            <h4 class="fw-bold">User's Information</h4>
            <div class="row mt-3">
                <!-- image -->
                <div class="col-3">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="img-fluid rounded-circle image-lg ms-5">
                    @else
                        <i class="fas fa-circle-user text-secondary icon-lg ms-5"></i>
                    @endif

                    <div class="mt-2 d-flex align-items-center gap-1 border border-1 ms-3">
                        <!-- Button(Add a picture) -->
                        <label for="avatar" class="btn btn-color2 btn-sm mb-0 align-middle">Add a picture</label>

                        <!-- hide the default label("No file chosen") -->
                        <input type="file" id="avatar" name="avatar" class="d-none"> 
                        
                        <!-- No picture yet -->
                        <span id="file-name" class="text-muted align-middle">No picture yet.</span>
                    </div>

                    <script>
                        document.getElementById('avatar').addEventListener('change', function(){
                            const fileName = this.files.length > 0 ? this.files[0].name : 'No picture yet.';
                            document.getElementById('file-name').textContent = fileName;
                        });
                    </script>
                </div>

                <!-- Name & Email -->
                <div class="col-4 ms-3">
                    <label for="name" class="form-label fs-5">Name</label>
                    <input type="text" id="name" class="form-control mb-2" name="name" value="{{ $user->name }}">
                    <label for="email" class="form-label fs-5">Email Address</label>
                    <input type="email" id="email" class="form-control mb-2" name="email" value="{{ $user->email }}">
                </div>

                <!-- Phone Number & Country -->
                <div class="col-4">
                    <label for="text" class="form-label fs-5">Phone Number (optional)</label>
                    <input type="text" id="phone" class="form-control mb-2" name="phone" value="{{ old('phone', auth()->user()->phone) }}">
                    <label for="country" class="form-label fs-5">Country (optional)</label>
                    <input type="text" id="country" class="form-control mb-2" name="country" value="{{ old('country', auth()->user()->country) }}">

                    <!-- Button -->
                    <div class="row g-2 mt-1">
                        <div class="col-6">
                            <a href="{{ route('profile.show', $user->id) }}" class="btn w-100 btn-white text-danger btn-sm border border-danger fw-bold">Cancel</a>    
                        </div>
                        <div class="col-6">
                            <button type="submit" class="btn w-100  btn-sm btn-color text-white fw-bold">Update Profile</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Change Password -->
    <form action="{{ route('profile.password.update') }}" method="post">
        @csrf
        @method('PATCH')
        <div class="card card-body border-dark bg-white w-80">
            <h4 class="fw-bold">Change Password</h4>

            <div class="row mt-3 g-3">
                <div class="col-4" style="margin-left: 40px;">
                    <label for="password" class="form-label fs-5">Current Password</label>
                    <input type="password" name="currentpassword" id="currentpassword" class="form-control w-75">
                </div>
                <div class="col-4">
                    <label for="password" class="form-label fs-5">New Password</label>
                    <input type="password" name="newpassword" id="newpassword" class="form-control w-75">
                </div>
                <div class="col-4" style="margin-right: -40px;">
                    <label for="password" class="form-label fs-5">Confirm Password</label>
                    <input type="password" name="newpassword_confirmation" id="confirmpassword" class="form-control mb-2 w-75">
                </div>
            </div>

            <div class="row g-2 mt-1">
                <div class="col-6 offset-6 text-end">
                    <button type="submit" class="btn btn-width btn-sm btn-color text-white fw-bold">Update Password</button>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection