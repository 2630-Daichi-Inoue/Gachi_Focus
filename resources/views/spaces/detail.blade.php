@extends('layouts.app')

@section('title', $space->name . ' | Coworking Space Detail')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/space-detail.css') }}">
@endsection

@section('content')

    <div class="container-fluid py-4">
        <div class="row">
            {{-- left --}}
            <div class="col-md-4 ">
                <div class="photo-gallery">
                    @forelse ($space->photos as $photo)
                        <img src="{{ asset('storage/' . $photo->path) }}" alt="Coworking photo">
                    @empty
                        <p>No photos available.</p>
                    @endforelse
                </div>
            </div>

            {{-- middle --}}
            <div class="col-md-4">
                <h1 class="fw-bold mb-3">{{ $space->name }}</h1>

                @php
                    $rating = round($space->rating ?? 0, 1);
                    $fullStars = floor($rating);
                    $halfStar = $rating - $fullStars >= 0.5 ? 1 : 0;
                    $emptyStars = 5 - $fullStars - $halfStar;
                @endphp

                <p>
                    @for ($i = 0; $i < $fullStars; $i++)
                        <i class="fa-solid fa-star"></i>
                    @endfor
                    @if ($halfStar)
                        <i class="fa-solid fa-star-half-stroke"></i>
                    @endif
                    @for ($i = 0; $i < $emptyStars; $i++)
                        <i class="fa-regular fa-star"></i>
                    @endfor
                    {{-- TODO: this link is commented out for now. Enable it once the review.index is ready. --}}
                    <span class="text-primary ps-3">reviews ></span>
                    {{-- <a href="{{ route('reviews.index', $space->id ?? 1) }}" class="text-primary">reviews ></a> --}}
                </p>

                <h4 class="fw-bold">Capacity</h4>
                <p>
                    <i class="fa-solid fa-people-group"></i>
                    {{ $space->capacity_min }} - {{ $space->capacity_max }}
                </p>

                <div class="mb-3 amenities-box">
                    <h4 class="fw-bold pt-3">Amenities</h4>
                    <div class="amenities-content">
                        <ul class="list-unstyled">
                            <li><strong>Facilities:</strong></li>
                            <ul>
                                @forelse ($space->facilities ?? [] as $facility)
                                    <li>{{ $facility }}</li>
                                @empty
                                    <li>No facilities registered</li>
                                @endforelse
                            </ul>

                            <li><strong>Description:</strong></li>
                            <ul>
                                <li>{!! nl2br(e($space->description)) !!}</li>
                            </ul>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- right --}}
            <div class="col-md-4 pe-3">
                <div class="mb-3">
                    @if (!empty($space->map_embed))
                        <iframe src="https://www.google.com/maps/embed?pb={{ $space->map_embed }}" width="100%"
                            height="250" style="border:0;" allowfullscreen="" loading="lazy">
                        </iframe>
                        {{-- DB side: When enlarge　-> &z=18 etc... --}}
                    @endif
                    <p class="map-address small text-muted mb-1">
                        <i class="fa-solid fa-location-dot me-1 text-muted"></i>
                        {{ $space->address ?? 'No address registered' }}
                    </p>
                    @if (!empty($space->address))
                        <a href="https://maps.google.com/?q={{ urlencode($space->address) }}" target="_blank"
                            class="text-primary" style="text-decoration: none;">
                            view in a map >
                        </a>
                    @endif
                </div>

                <div class="mt-4 price-section">
                    <h4 class="fw-bold mb-1">Price</h4>
                    <div class="d-flex justify-content-start gap-5">
                        <div>
                            <p class="mb-1">weekday :</p>
                            <p class="price-amount">${{ number_format($space->weekday_price, 2) }}/h</p>
                        </div>
                        <div>
                            <p class="mb-1">weekend :</p>
                            <p class="price-amount">${{ number_format($space->weekend_price, 2) }}/h</p>
                        </div>
                    </div>
                </div>

                {{-- TODO: this link is commented out for now. Enable it once the contact page is ready. --}}
                <p class="text-primary mt-4">contact us ></p>
                {{-- <a href="{{ route('contact') }}" class="text-primary d-block mb-3">Contact us</a> --}}

                <button class="btn btn-primary w-100"
                    style="background-color: rgba(84, 127, 161, 1); color: #fff; border: none;">Reserve</button>
            </div>
        </div>
    </div>
@endsection
