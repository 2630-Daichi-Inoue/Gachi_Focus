@extends('layouts.admin')

@section('title', 'Create an announcement')

<style>
    .input-unified {
        height: 36px;
    }
</style>

@section('content')
    <form action="{{ route('admin.announcements.store') }}" method="post" enctype="multipart/form-data">
        @csrf

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

        <div class="row mb-3">
            <div class="col-md-2">
                <label for="publishedDate" class="form-label fw-bold">
                    Published Date <span class="text-danger">※</span>
                </label>
                <input type="date" name="publishedDate" id="publishedDate" class="form-control input-unified " value="{{ old('publishedDate', date('Y-m-d', strtotime('+1 day'))) }}" min="{{ date('Y-m-d') }}">
                {{-- Error --}}
                @error('publishedDate')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>
            <div class="col-md-2">
                <label for="publishedTime" class="form-label fw-bold">
                    Published Time <span class="text-danger">※</span>
                </label>
                <input type="time" step="1800" name="publishedTime" id="publishedTime" class="form-control input-unified " value="{{ old('publishedTime', '06:00') }}" min="00:00" max="23:30">
                {{-- Error --}}
                @error('publishedTime')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <input type="hidden" name="isPublic" value="1">

        <!-- Create button -->
        <button type="submit" class="btn text-white fw-bold px-5" style="background-color: #757B9D">
            Create
        </button>

        {{-- @if ($errors->any())
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        @endif --}}
    </form>

@endsection
