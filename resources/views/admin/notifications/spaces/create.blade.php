@extends('layouts.admin')

@section('title', 'Admin: Create a space notification')

<style>
    .input-unified {
        height: 36px;
    }
</style>

@section('content')
    <h1>Create a space notification</h1>

    <form action="{{ route('admin.space-notifications.store', ['space' => $space->id]) }}" method="post" enctype="multipart/form-data">
        @csrf

        <input type="hidden" name="type" value="space">

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
        <p>Space: {{ $space->name }}</p>
        <p>Target Users: {{ $targetUsers->count() }}</p>
        <input type="hidden" name="targetUsers" value="{{ $targetUsers->implode(',') }}">
        <p>This notification will be sent to users with active reservations for this space.</p>
        <button type="submit"
                class="btn text-white fw-bold px-5"
                style="background-color: #757B9D;">
            Create
        </button>
        @endif

    </form>

@endsection
