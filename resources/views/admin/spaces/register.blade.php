@extends('layouts.app')

@section('title', 'Register space')

@section('content')
    <form action="{{ route('admin.spaces.store') }}" method="post" enctype="multipart/form-data">
        @csrf

        <div class="row mb-3">

            <div class="col-md-2">
                <label for="name" class="form-label fw-bold">
                    Name <span class="text-danger">※</span>
                </label>
                <input type="text" name="name" id="name"  class="form-control" value="{{ old('name') }}">
                {{-- Error --}}
                @error('name')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="prefecture" class="form-label fw-bold">
                    Prefecture <span class="text-danger">※</span>
                </label>
                    <select size="1" id="prefecture" name="prefecture">
                        <option value="Tokyo" {{ old('prefecture') === 'Tokyo' ? 'selected' : '' }}>Tokyo</option>
                        <option value="Kanagawa" {{ old('prefecture') === 'Kanagawa' ? 'selected' : '' }}>Kanagawa</option>
                        <option value="Chiba" {{ old('prefecture') === 'Chiba' ? 'selected' : '' }}>Chiba</option>
                        <option value="Saitama" {{ old('prefecture') === 'Saitama' ? 'selected' : '' }}>Saitama</option>
                        <option value="Osaka" {{ old('prefecture') === 'Osaka' ? 'selected' : '' }}>Osaka</option>
                        <option value="Hyogo" {{ old('prefecture') === 'Hyogo' ? 'selected' : '' }}>Hyogo</option>
                        <option value="Kyoto" {{ old('prefecture') === 'Kyoto' ? 'selected' : '' }}>Kyoto</option>
                    </select>
                {{-- Error --}}
                @error('prefecture')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-md-7">
                <label for="city" class="form-label fw-bold">
                    City / Ward <span class="text-danger">※</span>
                </label>
                <input type="text" name="city" id="city"  class="form-control" value="{{ old('city') }}">
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
                <input type="text" name="address_line" id="address_line"  class="form-control" value="{{ old('address_line') }}">
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
                <input type="number" name="capacity" id="capacity"  class="form-control" value="{{ old('capacity') }}" min="1" step="1">
                {{-- Error --}}
                @error('capacity')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-md-2">
                <label for="open_time" class="form-label fw-bold">
                    Open Time <span class="text-danger">※</span>
                </label>
                <input type="time" name="open_time" id="open_time" class="form-control" value="{{ old('open_time') }}">
                {{-- Error --}}
                @error('open_time')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-md-2">
                <label for="close_time" class="form-label fw-bold">
                    Close Time <span class="text-danger">※</span>
                </label>
                <input type="time" name="close_time" id="close_time" class="form-control" value="{{ old('close_time') }}">
                {{-- Error --}}
                @error('close_time')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-md-2">
                <label for="weekday_price_yen" class="form-label fw-bold">
                    Weekday Price (¥ / 30min) <span class="text-danger">※</span>
                </label>
                <input type="number" name="weekday_price_yen" id="weekday_price_yen" class="form-control" value="{{ old('weekday_price_yen') }}" min="1" step="1">
                {{-- Error --}}
                @error('weekday_price_yen')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-md-2">
                <label for="weekend_price_yen" class="form-label fw-bold">
                    Weekend Price (¥ / 30min) <span class="text-danger">※</span></label>
                <input type="number" name="weekend_price_yen" id="weekend_price_yen" class="form-control" value="{{ old('weekend_price_yen') }}" min="1" step="1">
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
            <label for="amenity" class="form-label d-block fw-bold">
                Amenity <span class="text-muted fw-normal"></span>
            </label>

            @foreach ($all_amenities as $amenity)
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
            <input type="file" name="image" id="image" class="form-control">
            <div class="form-text">
                Acceptable formats: jpeg, jpg, png, webp /  Max 1MB<br>
            </div>
            {{-- Error --}}
            @error('image')
                <p class="text-danger small">{{ $message }}</p>
            @enderror
        </div>

        <input type="hidden" name="is_public" value="1">

        <button type="submit" class="btn text-white fw-bold px-5" style="background-color: #757B9D">
            Register
        </button>
    </form>

@endsection
