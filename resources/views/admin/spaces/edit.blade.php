@extends('layouts.app')

@section('title', 'Edit Space')

@section('content')
    <form action="{{ route('admin.spaces.update', $space->id) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

            <div class="row mb-3">
                <div class="col-md-2">
                    <label for="name" class="form-label fw-bold">Name <span class="text-danger">※</span></label>
                    <input type="text" name="name" id="name"  class="form-control" value="{{ old('name', $space->name) }}">
                    {{-- Error --}}
                    @error('name')
                        <p class="text-danger small">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="location_for_overview" class="form-label fw-bold">Location for overview <span class="text-danger">※</span></label>
                    <input type="text" name="location_for_overview" id="location_for_overview"  class="form-control" value="{{ old('location_for_overview', $space->location_for_overview) }}">
                    {{-- Error --}}
                    @error('location_for_overview')
                        <p class="text-danger small">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-md-7">
                    <label for="location_for_details" class="form-label fw-bold">Location for details <span class="text-danger">※</span></label>
                    <input type="text" name="location_for_details" id="location_for_details"  class="form-control" value="{{ old('location_for_details', $space->location_for_details) }}">
                    {{-- Error --}}
                    @error('location_for_details')
                        <p class="text-danger small">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-2">
                    <label for="min_capacity" class="form-label fw-bold">Capacity(min) <span class="text-danger">※</span></label>
                    <input type="number" name="min_capacity" id="min_capacity"  class="form-control" value="{{ old('min_capacity', $space->min_capacity) }}" min="1" max="99" step="1">
                    {{-- Error --}}
                    @error('min_capacity')
                        <p class="text-danger small">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-md-2">
                    <label for="max_capacity" class="form-label fw-bold">Capacity(max) <span class="text-danger">※</span></label>
                    <input type="number" name="max_capacity" id="max_capacity"  class="form-control" value="{{ old('max_capacity', $space->max_capacity) }}" min="1" max="99" step="1">
                    {{-- Error --}}
                    @error('max_capacity')
                        <p class="text-danger small">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-md-2">
                    <label for="area" class="form-label fw-bold">Area(m2) <span class="text-danger">※</span></label>
                    <input type="number" name="area" id="area"  class="form-control" value="{{ old('area', $space->area) }}" min="1" step="0.5">
                    {{-- Error --}}
                    @error('area')
                        <p class="text-danger small">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-md-2">
                    <label for="weekday_price" class="form-label fw-bold">Weekday Price($ / h) <span class="text-danger">※</span></label>
                    <input type="number" name="weekday_price" id="weekday_price" class="form-control" value="{{ old('weekday_price', $space->weekday_price) }}" min="1" max="999999" step="1">
                    {{-- Error --}}
                    @error('weekday_price')
                        <p class="text-danger small">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-md-2">
                    <label for="weekend_price" class="form-label fw-bold">Weekend Price($ / h) <span class="text-danger">※</span></label>
                    <input type="number" name="weekend_price" id="weekend_price" class="form-control" value="{{ old('weekend_price', $space->weekend_price) }}" min="1" max="999999" step="1">
                    {{-- Error --}}
                    @error('weekend_price')
                        <p class="text-danger small">{{ $message }}</p>
                    @enderror
                </div>
                
            </div>

            <div class="mb-3">
                <label for="description" class="form-label fw-bold">Description <span class="text-danger">※</span></label>
                <textarea name="description" id="description" rows="3" class="form-control">{{ old('description', $space->description) }}</textarea>
                {{-- Error --}}
                @error('description')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3">
                <label for="category" class="form-label d-block fw-bold">
                    Category <span class="text-muted fw-normal"></span>
                </label>

                @php
                    // Prefer old() when validation failed; otherwise use DB-selected IDs
                    $checked = collect(old('category', $selected_categories ?? []))
                                ->map(fn($v) => (int) $v)
                                ->all();
                @endphp

                @foreach ($all_categories as $category)
                    <div class="form-check form-check-inline">
                        <input type="checkbox"
                                name="category[]"
                                id="{{ $category->name }}"
                                value="{{ $category->id }}" class="form-check-input"
                                {{ in_array($category->id, $checked, true) ? 'checked' : '' }}>
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
                 @if ($space->image)
                <div class="mt-2">
                    <img src="{{ $space->image }}" alt="current image" class="img-fluid border" style="max-height:120px; object-fit:cover;">
                    <div class="text-muted small">Current image (kept if you don't upload a new one)</div>
                </div>
                @endif
                {{-- Error --}}
                @error('image')
                    <p class="text-danger small">{{ $message }}</p>
                @enderror
            </div>

            <!-- update button -->
            <button type="submit" class="btn text-white fw-bold px-5" style="background-color: #757B9D">
                Update
            </button>

            <!-- delete or rctivate button -->
            @if ($space->trashed())
                {{-- reactivate button --}}
                <button type="button"
                        class="btn btn-outline-success fw-bold px-5"
                        data-bs-toggle="modal"
                        data-bs-target="#confirmActivateModal">
                    Activate
                </button>
            @else
                <button type="button"
                        class="btn btn-outline-danger fw-bold px-5"
                        data-bs-toggle="modal"
                        data-bs-target="#confirmDeleteModal">
                    Delete
                </button>
            @endif
    </form>

    <form id="delete-space-form"
            action="{{ route('admin.spaces.destroy', $space->id) }}"
            method="POST"
            class="d-none">
        @csrf
        @method('DELETE')
    </form>

    <div class="modal fade"
        id="confirmDeleteModal"
        tabindex="-1"
        aria-labelledby="confirmDeleteLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centred">
            <div class="modal-content border-danger">
                <div class="modal-header border-danger">
                    <h5 class="modal-title text-danger"
                        id="confirmDeleteLabel">Delete space</h5>
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete <strong>{{ $space->name }}</strong>?
                </div>
                <div class="modal-footer border-0">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel
                    </button>
                    <button type="button"
                            class="btn btn-danger"
                            onclick="document.getElementById('delete-space-form').submit();"
                            data-bs-dismiss="modal">Yes, delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade"
        id="confirmActivateModal"
        tabindex="-1"
        aria-labelledby="confirmActivateLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centred">
            <div class="modal-content border-success">
                <div class="modal-header border-success">
                    <h5 class="modal-title text-success" id="confirmActivateLabel">
                        Activate space
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Do you want to activate <strong>{{ $space->name }}</strong>?
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button"
                            class="btn btn-success"
                            onclick="document.getElementById('delete-space-form').submit();"
                            data-bs-dismiss="modal">
                        Yes, activate
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection