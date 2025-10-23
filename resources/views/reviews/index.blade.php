@extends('layouts.app')

@section('title', 'Reviews')

@section('content')
    <div class="container mt-5">
        <div class="row">
            {{-- left column --}}
            <div class="col-md-4 mb-4">
                <h2 class="fw-bold mb-3">Reviews</h2>

                {{-- button --}}
                <button class="btn btn-outline-dark w-75 mb-4" data-bs-toggle="modal" data-bs-target="#writeReviewModal">
                    <i class="fa-solid fa-pen me-2"></i> Write a review
                </button>

                {{-- Avarage Rating --}}
                @php
                    $avg = $averageRating ?? 0;
                    $fullStars = floor($avg);
                    $halfStar = $avg - $fullStars >= 0.5 ? 1 : 0;
                    $emptyStars = 5 - $fullStars - $halfStar;
                @endphp

                <div class="mb-4">
                    <p class="text-secondary small mt-1 mb-0">Average of all reviews</p>
                    @for ($i = 0; $i < $fullStars; $i++)
                        <i class="fa-solid fa-star text-warning"></i>
                    @endfor
                    @if ($halfStar)
                        <i class="fa-solid fa-star-half-stroke text-warning"></i>
                    @endif
                    @for ($i = 0; $i < $emptyStars; $i++)
                        <i class="fa-regular fa-star text-warning"></i>
                    @endfor
                    <span class="fs-4 ms-2 fw-bold">{{ number_format($avg, 1) }}</span>
                </div>

                {{-- Detail Ratings --}}
                <div class="mb-2">
                    <p class="mb-1 fw-semibold">Cleanliness</p>
                    <div>
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="fa-star {{ $i <= round($cleanliness) ? 'fa-solid' : 'fa-regular' }}"
                                style="color:#FFD966"></i>
                        @endfor
                        <span class="ms-2">{{ $cleanliness ?? '0.0' }}</span>
                    </div>
                </div>

                <div class="mb-2">
                    <p class="mb-1 fw-semibold">Property conditions</p>
                    <div>
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="fa-star {{ $i <= round($conditions) ? 'fa-solid' : 'fa-regular' }}"
                                style="color:#FFD966"></i>
                        @endfor
                        <span class="ms-2">{{ $conditions ?? '0.0' }}</span>
                    </div>
                </div>

                <div class="mb-2">
                    <p class="mb-1 fw-semibold">Facilities</p>
                    <div>
                        @for ($i = 1; $i <= 5; $i++)
                            <i class="fa-star {{ $i <= round($facilities) ? 'fa-solid' : 'fa-regular' }}"
                                style="color:#FFD966"></i>
                        @endfor
                        <span class="ms-2">{{ $facilities ?? '0.0' }}</span>
                    </div>
                </div>
            </div>


            {{-- Right Column --}}
            <div class="col-md-8">

                <form method="GET" action="{{ route('reviews.index', $reservation->id) }}"
                    class="d-flex gap-3 mb-4 w-100">

                    {{-- Sort Select --}}
                    <select name="sort" class="form-select" style="flex: 0 0 30%;" onchange="this.form.submit()">
                        <option value="recent" {{ request('sort') === 'recent' ? 'selected' : '' }}>Most Recent</option>
                        <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="highest" {{ request('sort') === 'highest' ? 'selected' : '' }}>Highest Rated
                        </option>
                        <option value="lowest" {{ request('sort') === 'lowest' ? 'selected' : '' }}>Lowest Rated</option>
                        <option value="with" {{ request('sort') === 'with' ? 'selected' : '' }}>With Photo</option>
                    </select>

                    {{-- Search Bar --}}
                    <div class="input-group" style="width: 300px;">
                        <input type="text" name="search" class="form-control" placeholder="Search Reviews"
                            value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </form>

                {{-- Review List --}}
                @forelse ($reviews as $review)
                    @php
                        $rating = round(($review->cleanliness + $review->conditions + $review->facilities) / 3, 1);
                        $fullStars = floor($rating);
                        $halfStar = $rating - $fullStars >= 0.5 ? 1 : 0;
                        $emptyStars = 5 - $fullStars - $halfStar;
                    @endphp

                    <div class="border-bottom py-3 d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $review->user->name ?? 'Anonymous' }}</strong>
                            <span class="text-secondary ms-2">{{ $review->created_at->format('M d, Y') }}</span>

                            <div class="mt-1">
                                @for ($i = 0; $i < $fullStars; $i++)
                                    <i class="fa-solid fa-star text-warning"></i>
                                @endfor
                                @if ($halfStar)
                                    <i class="fa-solid fa-star-half-stroke text-warning"></i>
                                @endif
                                @for ($i = 0; $i < $emptyStars; $i++)
                                    <i class="fa-regular fa-star text-warning"></i>
                                @endfor
                                <span class="ms-2">{{ $rating }}</span>

                                @if ($review->comment)
                                    <p class="mb-0 mt-1">{{ $review->comment }}</p>
                                @endif
                            </div>

                            @if ($review->photo)
                                <div style="padding: 6px; border-radius: 8px; display: inline-block; max-width: 180px;">
                                    <img src="{{ asset('storage/' . $review->photo) }}" alt="Review photo"
                                        style="width: 100%; height: auto; border-radius: 8px;
                                        opacity: 0.95; display: block; object-fit: contain;">
                                </div>
                            @endif
                        </div>

                        {{-- buttons --}}
                        @if (Auth::check() && Auth::id() === $review->user_id)
                            <div class="text-end">
                                <button class="btn btn-success btn-sm mb-2 w-100" data-bs-toggle="modal"
                                    data-bs-target="#writeReviewModal" data-review-id="{{ $review->id }}"
                                    data-rating="{{ $review->rating }}" data-cleanliness="{{ $review->cleanliness }}"
                                    data-conditions="{{ $review->conditions }}"
                                    data-facilities="{{ $review->facilities }}" data-comment="{{ $review->comment }}"
                                    data-photo="{{ $review->photo }}">
                                    Edit
                                </button>
                                <button class="btn btn-outline-danger btn-sm w-100" data-bs-toggle="modal"
                                    data-bs-target="#deleteReviewModal{{ $review->id }}">
                                    Delete
                                </button>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteReviewModal{{ $review->id }}" tabindex="-1"
                                    aria-labelledby="deleteReviewModalLabel{{ $review->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content p-4 border-0 shadow-sm">

                                            {{-- Header --}}
                                            <div class="modal-header border-0 pb-0">
                                                <h4 class="modal-title fw-bold text-dark">Delete Review</h4>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>

                                            {{-- Body --}}
                                            <div class="modal-body text-start">
                                                <p class="fw-semibold mb-2" style="font-size:1.05rem; margin-left:2px;">
                                                    Are you sure you want to delete this review?
                                                </p>
                                                <p class="text-secondary small mb-4" style="margin-left:2px;">
                                                    This action cannot be undone.
                                                </p>

                                                {{-- Action Buttons --}}
                                                <form action="{{ route('reviews.destroy', $review->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')

                                                    <div class="d-flex justify-content-end gap-2">
                                                        <button type="button" class="btn px-4 fw-semibold"
                                                            data-bs-dismiss="modal"
                                                            style="color: #555; border: 1.5px solid #ccc; background: #f8f8f8;">
                                                            Cancel
                                                        </button>

                                                        <button type="submit" class="btn text-white px-4 fw-semibold"
                                                            style="background-color: rgba(166, 75, 75, 1); border: none;">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-secondary mt-3">No reviews yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    @include('reviews.modal')
@endsection
