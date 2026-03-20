@extends('layouts.admin')

@section('title', 'Edit Space')

@section('content')
    <form action="{{ route('admin.spaces.update', $space) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <div class="row mb-3">
            <div class="col-md-2">
                <label for="name" class="form-label fw-bold">
                    Name <span class="text-danger">※</span>
                </label>
                <input type="text" name="name" id="name"  class="form-control" value="{{ old('name', $space->name) }}">
                {{-- Error --}}
                @error('name')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-md-3">
                <label for="prefecture" class="form-label fw-bold">
                    Prefecture <span class="text-danger">※</span>
                </label>
                <select size="1" id="prefecture" name="prefecture" class="form-control input-unified">
                    @foreach (config('constants.prefectures') as $major_or_other => $prefectures)
                        <optgroup label="{{ $major_or_other }}">
                            @foreach ($prefectures as $prefecture)
                                <option value="{{ $prefecture }}" {{ old('prefecture', $space->prefecture) === $prefecture ? 'selected' : '' }}>
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

            <div class="col-md-7">
                <label for="city" class="form-label fw-bold">
                    City / Ward <span class="text-danger">※</span>
                </label>
                <input type="text" name="city" id="city"  class="form-control" value="{{ old('city', $space->city) }}">
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
                <input type="text" name="address_line" id="address_line"  class="form-control" value="{{ old('address_line', $space->address_line) }}">
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
                <input type="number" name="capacity" id="capacity"  class="form-control" value="{{ old('capacity', $space->capacity) }}" min="1" step="1">
                {{-- Error --}}
                @error('capacity')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-md-2">
                <label for="open_time" class="form-label fw-bold">
                    Open Time <span class="text-danger">※</span>
                </label>
                <input type="time" name="open_time" id="open_time" class="form-control" value="{{ old('open_time', $space->open_time_for_form) }}">
                {{-- Error --}}
                @error('open_time')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-md-2">
                <label for="close_time" class="form-label fw-bold">
                    Close Time <span class="text-danger">※</span>
                </label>
                <input type="time" name="close_time" id="close_time" class="form-control" value="{{ old('close_time', $space->close_time_for_form) }}">
                {{-- Error --}}
                @error('close_time')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-md-2">
                <label for="weekday_price_yen" class="form-label fw-bold">
                    Weekday Price (¥ / 30min) <span class="text-danger">※</span>
                </label>
                <input type="number" name="weekday_price_yen" id="weekday_price_yen" class="form-control" value="{{ old('weekday_price_yen', $space->weekday_price_yen) }}" min="1" step="1">
                {{-- Error --}}
                @error('weekday_price_yen')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-md-2">
                <label for="weekend_price_yen" class="form-label fw-bold">
                    Weekend Price (¥ / 30min) <span class="text-danger">※</span></label>
                <input type="number" name="weekend_price_yen" id="weekend_price_yen" class="form-control" value="{{ old('weekend_price_yen', $space->weekend_price_yen) }}" min="1" step="1">
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
            <textarea name="description" id="description" rows="4" class="form-control">{{ old('description', $space->description) }}</textarea>
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
                        id="amenity_{{ $amenity->id }}"
                        value="{{ $amenity->id }}"
                        class="form-check-input"
                        {{ in_array($amenity->id, old('amenities', $selectedAmenityIds)) ? 'checked' : '' }}
                    >
                    <label for="amenity_{{ $amenity->id }}" class="form-check-label">
                        {{ $amenity->name }}
                    </label>
                </div>
            @endforeach
            {{-- Error --}}
            @error('amenities')
                <p class="text-danger small">{{ $message }}</p>
            @enderror
        </div>

         <div class="mb-3">
            @if ($space->image_path)
                <div class="mb-2">
                    <p class="small text-muted">Current image:</p>
                    <img src="{{ Storage::url($space->image_path) }}" style="max-height: 150px;">
                </div>
            @endif
            <label for="image" class="form-label d-block fw-bold">
                Image
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

        <!-- update button -->
        <button type="submit" class="btn text-white fw-bold px-5" style="background-color: #757B9D">
            Update
        </button>

        <!-- delete button -->
        <button type="button"
                class="btn btn-outline-danger fw-bold px-5"
                data-bs-toggle="modal"
                data-bs-target="#confirmDeleteModal">
            Delete
        </button>
    </form>

    <form id="delete-space-form"
            action="{{ route('admin.spaces.destroy', $space) }}"
            method="POST"
            class="d-none">
        @csrf
        @method('DELETE')
    </form>

    @include('admin.spaces.modals.delete')

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    @endif

@endsection
