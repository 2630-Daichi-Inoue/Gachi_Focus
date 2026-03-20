@extends('layouts.admin')

@section('title', 'Register space')

<style>
    .input-unified {
        height: 36px;
    }
</style>

@section('content')
    <form action="{{ route('admin.spaces.store') }}" method="post" enctype="multipart/form-data">
        @csrf

        <div class="row mb-3">
            <div class="col-md-2">
                <label for="name" class="form-label fw-bold">
                    Name <span class="text-danger">※</span>
                </label>
                <input type="text" name="name" id="name"  class="form-control input-unified " value="{{ old('name') }}">
                {{-- Error --}}
                @error('name')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-md-2">
                <label for="prefecture" class="form-label fw-bold">
                    Prefecture <span class="text-danger">※</span>
                </label>
                    <select size="1" id="prefecture" name="prefecture" class="form-control input-unified">
                        @foreach (config('constants.prefectures') as $major_or_other => $prefectures)
                            <optgroup label="{{ $major_or_other }}">
                                @foreach ($prefectures as $prefecture)
                                    <option value="{{ $prefecture }}" {{ old('prefecture') === $prefecture ? 'selected' : '' }}>
                                        {{ $prefecture }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                {{-- Error --}}
                @error('prefecture')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-md-4">
                <label for="city" class="form-label fw-bold">
                    City / Ward <span class="text-danger">※</span>
                </label>
                <input type="text" name="city" id="city" class="form-control input-unified " value="{{ old('city') }}">
                {{-- Error --}}
                @error('city')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="address_line" class="form-label fw-bold">
                    Address Line <span class="text-danger">※</span>
                </label>
                <input type="text" name="address_line" id="address_line" class="form-control input-unified " value="{{ old('address_line') }}">
                {{-- Error --}}
                @error('address_line')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-2">
                <label for="capacity" class="form-label fw-bold">
                    Capacity <span class="text-danger">※</span>
                </label>
                <input type="number" name="capacity" id="capacity" class="form-control input-unified " value="{{ old('capacity') }}" min="1" step="1">
                {{-- Error --}}
                @error('capacity')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-md-2">
                <label for="open_time" class="form-label fw-bold">
                    Open Time <span class="text-danger">※</span>
                </label>
                <input type="time" step="1800" name="open_time" id="open_time" class="form-control input-unified " value="{{ old('open_time') }}">
                {{-- Error --}}
                @error('open_time')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-md-2">
                <label for="close_time" class="form-label fw-bold">
                    Close Time <span class="text-danger">※</span>
                </label>
                <input type="time" step="1800" name="close_time" id="close_time" class="form-control input-unified " value="{{ old('close_time') }}">
                {{-- Error --}}
                @error('close_time')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-md-2">
                <label for="weekday_price_yen" class="form-label fw-bold">
                    Weekday Price (¥ / 30min) <span class="text-danger">※</span>
                </label>
                <input type="number" name="weekday_price_yen" id="weekday_price_yen" class="form-control input-unified " value="{{ old('weekday_price_yen') }}" min="1" step="1">
                {{-- Error --}}
                @error('weekday_price_yen')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-md-2">
                <label for="weekend_price_yen" class="form-label fw-bold">
                    Weekend Price (¥ / 30min) <span class="text-danger">※</span>
                </label>
                <input type="number" name="weekend_price_yen" id="weekend_price_yen" class="form-control input-unified " value="{{ old('weekend_price_yen') }}" min="1" step="1">
                {{-- Error --}}
                @error('weekend_price_yen')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label fw-bold">
                Description <span class="text-danger">※</span>
            </label>
            <textarea name="description" id="description" rows="4" class="form-control">{{ old('description') }}</textarea>
            {{-- Error --}}
            @error('description')
                <p class="text-danger small">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label d-block fw-bold">
                Amenity <span class="text-muted fw-normal"></span>
            </label>

            @foreach ($amenities as $amenity)
                <div class="form-check form-check-inline">
                    <input
                        type="checkbox"
                        name="amenities[]"
                        id="{{ $amenity->id }}"
                        value="{{ $amenity->id }}"
                        class="form-check-input"
                        {{ in_array($amenity->id, old('amenities', [])) ? 'checked' : '' }}
                    >
                    <label for="{{ $amenity->id }}" class="form-check-label">
                        {{ $amenity->name }}
                    </label>
                </div>
            @endforeach
            {{-- Error --}}
            @error('amenities')
                <p class="text-danger small">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="image" class="form-label fw-bold">
                Image <span class="text-danger">※</span>
            </label>
            <input type="file" name="image" id="image" class="form-control input-unified ">
            <div class="form-text">
                Acceptable formats: jpeg, jpg, png, webp /  Max 1MB<br>
            </div>
            {{-- Error --}}
            @error('image')
                <p class="text-danger small">{{ $message }}</p>
            @enderror
        </div>

        <input type="hidden" name="is_public" value="1">

        <!-- register button -->
        <button type="submit" class="btn text-white fw-bold px-5" style="background-color: #757B9D">
            Register
        </button>

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        @endif
    </form>

@endsection
