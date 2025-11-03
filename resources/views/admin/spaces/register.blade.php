@extends('layouts.app')

@section('title', 'Register space')

@section('content')
    <form action="{{ route('admin.space.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row mb-3">
            <div class="col-md-2">
                <label for="name" class="form-label fw-bold">Name <span class="text-danger">※</span></label>
                <input type="text" name="name" id="name"  class="form-control" value="{{ old('name') }}">
                {{-- Error --}}
                @error('name')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="col-md-3">
                <label for="location_for_overview" class="form-label fw-bold">Location for overview <span class="text-danger">※</span></label>
                <input type="text" name="location_for_overview" id="location_for_overview"  class="form-control" value="{{ old('location_for_overview') }}">
                {{-- Error --}}
                @error('location_for_overview')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-md-7">
                <label for="location_for_details" class="form-label fw-bold">Location for details <span class="text-danger">※</span></label>
                <input type="text" name="location_for_details" id="location_for_details"  class="form-control" value="{{ old('location_for_details') }}">
                {{-- Error --}}
                @error('location_for_details')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-2">
                <label for="min_capacity" class="form-label fw-bold">Capacity(min) <span class="text-danger">※</span></label>
                <input type="number" name="min_capacity" id="min_capacity"  class="form-control" value="{{ old('min_capacity') }}" min="1" max="99" step="1">
                {{-- Error --}}
                @error('min_capacity')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-md-2">
                <label for="max_capacity" class="form-label fw-bold">Capacity(max) <span class="text-danger">※</span></label>
                <input type="number" name="max_capacity" id="max_capacity"  class="form-control" value="{{ old('max_capacity') }}" min="1" max="99" step="1">
                {{-- Error --}}
                @error('max_capacity')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-md-2">
                <label for="max_capacity" class="form-label fw-bold">Area(m2) <span class="text-danger">※</span></label>
                <input type="number" name="area" id="area"  class="form-control" value="{{ old('area') }}" min="1" step="0.5">
                {{-- Error --}}
                @error('area')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-md-2">
                <label for="weekday_rice" class="form-label fw-bold">Weekday Price(¥ / h) <span class="text-danger">※</span></label>
                <input type="number" name="weekday_price" id="weekday_price" class="form-control" value="{{ old('weekday_price') }}" min="1" max="999999" step="1">
                {{-- Error --}}
                @error('weekday_price')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-md-2">
                <label for="weekend_price" class="form-label fw-bold">Weekend Price($ / h) <span class="text-danger">※</span></label>
                <input type="number" name="weekend_price" id="weekend_price" class="form-control" value="{{ old('weekend_price') }}" min="1" max="999999" step="1">
                {{-- Error --}}
                @error('weekend_price')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>
            
        </div>

        <div class="mb-3">
            <label for="description" class="form-label fw-bold">Description <span class="text-danger">※</span></label>
            <textarea name="description" id="description" rows="3" class="form-control">{{ old('description') }}</textarea>
            {{-- Error --}}
            @error('description')
                <p class="text-danger small">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-3">
            <label for="category" class="form-label d-block fw-bold">
                Category <span class="text-muted fw-normal"></span>
            </label>

            @foreach ($all_categories as $category)
                <div class="form-check form-check-inline">
                    <input type="checkbox" name="category[]" id="{{ $category->name }}" value="{{ $category->id }}" class="form-check-input">
                    <label for="{{ $category->name }}" class="form-check-label">{{ $category->name }}</label>
                </div>
            @endforeach
            {{-- Error --}}
            @error('category')
                <p class="text-danger small">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="image" class="form-label fw-bold">Image <span class="text-danger">※</span></label>
            <input type="file" name="image" id="image" aria-describedby="image-info" class="form-control">
            <div class="form-text" id="image-info">
                The acceptable formats are jpeg, jpg, png, and gif only <br>
                Max file size is 1048kb.
            </div>
            {{-- Error --}}
            @error('image')
                <p class="text-danger small">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn text-white fw-bold px-5" style="background-color: #757B9D">Register</button>
    </form>

@endsection