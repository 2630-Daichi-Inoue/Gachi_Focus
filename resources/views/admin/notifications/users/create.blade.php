@extends('layouts.admin')

@section('title', 'Admin: Create a user notification')

<style>
    .input-unified {
        height: 36px;
    }
</style>

@section('content')
    <h1>Create a user notification</h1>

    <form action="{{ route('admin.user-notifications.store', ['user' => $user->id]) }}" method="post" enctype="multipart/form-data">
        @csrf

        <input type="hidden" name="type" value="user">

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="title" class="form-label fw-bold">
                    Title <span class="text-danger">※</span>
                </label>
                <input type="text" name="title" id="title" class="form-control input-unified " value="{{ old('title') }}">
                {{-- Error --}}
                @error('title')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="message" class="form-label fw-bold">
                Message <span class="text-danger">※</span>
            </label>
            <textarea name="message" id="message" rows="4" class="form-control">{{ old('message') }}</textarea>
            {{-- Error --}}
            @error('message')
                <p class="text-danger small">{{ $message }}</p>
            @enderror
        </div>

        {{-- Display message if no users have upcoming reservations --}}
        @if ($message)
            <p class="text-danger small">{{ $message }}</p>
        @else
        <!-- Create button -->
        <p>User: {{ $user->name }}</p>
        <input type="hidden" name="user" value="{{ $user->id }}">
        <p>This notification will be sent to the selected user.</p>
        <button type="submit"
                class="btn text-white fw-bold px-5"
                style="background-color: #757B9D;">
            Create
        </button>
        @endif

    </form>

@endsection
