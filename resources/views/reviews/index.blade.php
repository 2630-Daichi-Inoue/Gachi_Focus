@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <h4>Reviews</h4>
            <h2>★ {{ number_format($averageRating, 1) }}</h2>

            <p>Cleanliness: ★ {{ number_format($ratings['cleanliness'] ?? 0, 1) }}</p>
            <p>Property conditions: ★ {{ number_format($ratings['property'] ?? 0, 1) }}</p>
            <p>Facilities: ★ {{ number_format($ratings['facilities'] ?? 0, 1) }}</p>

            <a href="{{ route('spaces.reviews.create', $space->id) }}" class="btn btn-outline-dark mt-3">
                Write a review
            </a>
        </div>

        <div class="col-md-9">
            <div class="d-flex justify-content-between mb-3">
                <form method="GET" class="d-flex">
                    <select name="sort" class="form-select me-2" onchange="this.form.submit()">
                        <option value="recent" {{ request('sort')=='recent' ? 'selected' : '' }}>Most Recent</option>
                        <option value="high" {{ request('sort')=='high' ? 'selected' : '' }}>Highest Rated</option>
                        <option value="low" {{ request('sort')=='low' ? 'selected' : '' }}>Lowest Rated</option>
                    </select>
                    <input type="text" name="search" class="form-control" placeholder="Search Reviews"
                           value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary ms-2">🔍</button>
                </form>
            </div>

            @forelse($space->reviews as $review)
                <div class="border-bottom mb-3 pb-2">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>{{ $review->user->name }}</strong>
                            <span class="text-muted">{{ $review->created_at->format('M d, Y') }}</span>
                            <div>★ {{ $review->rating }}</div>
                        </div>

                        @if(auth()->id() === $review->user_id)
                            <div>
                                <a href="{{ route('reviews.edit', [$space->id, $review->id]) }}" class="btn btn-sm btn-success">Edit</a>
                                <form action="{{ route('reviews.destroy', [$space->id, $review->id]) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </div>
                        @endif
                    </div>
                    <p class="mt-2">{{ $review->comment }}</p>
                </div>
            @empty
                <p>No reviews yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection