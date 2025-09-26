@extends('layouts.app')

@section('title', 'Home')

@section('content')
<form method="GET" action="{{ route('search') }}" id="searchForm">
    <div class="row mb-2">

        <div class="col-md-6"></div>

        <!-- "Clear Filters" goes back to initial (no filters) -->
        <div class="col-md-2 mb-2">
            <a href="{{ route('index') }}"
                class="bg-secondary-subtle border border-dark rounded px-3 py-1 d-inline-block text-center text-dark text-decoration-none w-100">
                Clear Filters
            </a>
        </div>

        <div class="col-md-2"></div>

        <!-- "Search" submits the filters -->
        <div class="col-md-2 mb-2">
            <button type="submit"
                    class="border border-dark rounded px-3 py-1 text-white fw-bold w-100"
                    style="background-color: #757B9D; letter-spacing: 0.15em;">
                Search
            </button>
        </div>
        </div>

        <div class="row">
            <!-- Name -->
            <div class="col-md-2 mb-2">
                <input type="search"
                        name="name"
                        id="name"
                        class="form-control form-control-sm border border-dark"
                        placeholder="Name"
                        value="{{ request('name') }}">
                @error('name')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <!-- Location (overview/details) -->
            <div class="col-md-2 mb-2">
                <input type="search"
                        name="location"
                        id="location"
                        class="form-control form-control-sm border border-dark"
                        placeholder="Location"
                        value="{{ request('location') }}">
                @error('location')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <!-- Max Fee (free input). Empty = Any -->
            <div class="col-md-2 mb-2">
                <input type="number"
                        name="max_fee"
                        id="max_fee"
                        class="form-control form-control-sm border border-dark"
                        placeholder="Max Fee / h (Any)"
                        value="{{ request('max_fee') }}"
                        step="0.5"
                        min="0"
                        inputmode="decimal">
                @error('max_fee')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <!-- Capacity (free input). Empty = Any -->
            <div class="col-md-2 mb-2">
            <input type="number"
                    name="capacity"
                    id="capacity"
                    class="form-control form-control-sm border border-dark"
                    placeholder="Capacity (Any)"
                    value="{{ request('capacity') }}"
                    step="1"
                    min="1"
                    inputmode="numeric">
                @error('capacity')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-2 mb-2"></div>

            <div class="col-md-2 mb-2">
                <select name="sort"
                        id="sort"
                        class="form-select form-select-sm border border-dark text-dark">
                    <option value="rating_high_to_low" {{ request('sort','rating_high_to_low')==='rating_high_to_low' ? 'selected' : '' }}>
                        Rating: High → Low
                    </option>
                    <option value="price_high_to_low"    {{ request('sort')==='price_high_to_low'    ? 'selected' : '' }}>Price: High → Low</option>
                    <option value="price_low_to_high"    {{ request('sort')==='price_low_to_high'    ? 'selected' : '' }}>Price: Low → High</option>
                    <option value="capacity_high_to_low" {{ request('sort')==='capacity_high_to_low' ? 'selected' : '' }}>Capacity: High → Low</option>
                    <option value="capacity_low_to_high" {{ request('sort')==='capacity_low_to_high' ? 'selected' : '' }}>Capacity: Low → High</option>
                    <option value="newest"               {{ request('sort')==='newest'               ? 'selected' : '' }}>Newest First</option>
                </select>
                @error('sort')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

        </div>
    </form>

    <div class="row gx-2">

            @forelse ($home_spaces as $space)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        @include('users.spaces.contents.title')
                        @include('users.spaces.contents.body')
                        @include('users.spaces.contents.footer')
                    </div>
                </div>
            @empty       
                <div class="text-center">
                    <h2>No results.</h2>
                    <p class="text-secondary">Try different filters or remove them.</p>
                </div>
            @endforelse
        
    </div>

    <div class="row">

        <!-- showing the number of spaces -->
        <div class="col-md-6 d-flex align-items-center">
            @if ($home_spaces->isNotEmpty())
                <p class="mb-0">
                    Showing {{ $home_spaces->firstItem() }} - {{ $home_spaces->lastItem()}} of {{ $home_spaces->total() }}
                </p>
            @endif
        </div>

        <!-- pagenation -->
        <div class="col-md-6">
            <div class="d-flex justify-content-end">
                {{ $home_spaces->links() }}
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('searchForm');
    const sort = document.getElementById('sort');
    if (!form || !sort) return;

    sort.addEventListener('change', () => {
        // Reset pagination to page 1
        let page = form.querySelector('input[name="page"]');
        if (!page) {
        page = document.createElement('input');
        page.type = 'hidden';
        page.name = 'page';
        form.appendChild(page);
        }
        page.value = 1;

        form.submit();
    });
    });
    </script>

@endsection