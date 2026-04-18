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
                <label for="published_date" class="form-label fw-bold">
                    Published Date <span class="text-danger">※</span>
                </label>
                <input type="date" name="published_date" id="published_date" class="form-control input-unified " value="{{ old('published_date', date('Y-m-d', strtotime('+1 day'))) }}" min="{{ date('Y-m-d') }}">
                {{-- Error --}}
                @error('published_date')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>
            <div class="col-md-2">
                <label for="published_time" class="form-label fw-bold">
                    Published Time <span class="text-danger">※</span>
                </label>
                <input type="time" step="1800" name="published_time" id="published_time" class="form-control input-unified " value="{{ old('published_time', '06:00') }}" min="00:00" max="23:59">
                {{-- Error --}}
                @error('published_time')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-2">
                <label for="expired_date" class="form-label fw-bold">
                    Expired Date
                </label>
                <input type="date" name="expired_date" id="expired_date" class="form-control input-unified " value="{{ old('expired_date', '') }}" min="{{ date('Y-m-d') }}">
                {{-- Error --}}
                @error('expired_date')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>
            <div class="col-md-2">
                <label for="expired_time" class="form-label fw-bold">
                    Expired Time
                </label>
                <input type="time" step="1800" name="expired_time" id="expired_time" class="form-control input-unified " value="{{ old('expired_time', '') }}" min="00:00" max="23:59">
                {{-- Error --}}
                @error('expired_time')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <input type="hidden" name="is_public" value="1">

        <!-- Create button -->
        <button type="submit" class="btn text-white fw-bold px-5" style="background-color: #757B9D">
            Create
        </button>

    </form>

@endsection
