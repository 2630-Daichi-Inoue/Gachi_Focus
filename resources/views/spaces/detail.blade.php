@extends('layouts.app')

@section('title', $space->name . ' | Coworking Space Detail')

@section('content')

    <div class="container-fluid py-4" style="height: 100vh; overflow: hidden; position: fixed; top: 95px; left: 0; right: 0;">
        <div class="row" style="display: flex; align-items: stretch; height: calc(100vh - 150px); overflow: hidden;">

            {{-- left --}}
            <div class="col-md-4"
                style="overflow-y: auto; height: 100%; padding-right: 8px; display: flex; flex-direction: column; gap: 8px; background: #fff;">
                @forelse ($space->photos as $photo)
                    <img src="{{ asset('storage/' . $photo->path) }}" alt="Coworking photo"
                        style="width: 100%; height: 230px; object-fit: cover; border-radius: 8px;
                    {{ $loop->last ? 'margin-bottom:0;' : 'margin-bottom:4px;' }} transition: transform 0.3s ease; cursor: pointer;"
                        onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                @empty
                    <p>No photos available.</p>
                @endforelse
            </div>

            {{-- middle --}}
            <div class="col-md-4"
                style="padding-left: 16px; display: flex; flex-direction: column; justify-content: flex-start;">
                <div>
                    <h2 class="fw-bold mb-3">{{ $space->name }}</h2>

                    @php
                        $rating = round($space->rating ?? 0, 1);
                        $fullStars = floor($rating);
                        $halfStar = $rating - $fullStars >= 0.5 ? 1 : 0;
                        $emptyStars = 5 - $fullStars - $halfStar;
                    @endphp

                    @if ($reviewCount > 0)
                        <p style="margin-bottom: 10px;">
                            @for ($i = 0; $i < $fullStars; $i++)
                                <i class="fa-solid fa-star text-warning"></i>
                            @endfor
                            @if ($halfStar)
                                <i class="fa-solid fa-star-half-stroke text-warning"></i>
                            @endif
                            @for ($i = 0; $i < $emptyStars; $i++)
                                <i class="fa-regular fa-star text-warning"></i>
                            @endfor
                            <span style="color:#555; font-size:0.95rem; margin-left:4px;">
                                {{ $rating }} / 5
                            </span>
                            <a href="{{ route('reviews.index', $space->id) }}"
                                style="color:#547fa1; text-decoration:none; margin-left:8px;">reviews ></a>
                        </p>
                    @else
                        <p style="color:#888; font-style:italic; margin-bottom:10px;">No reviews</p>
                    @endif

                    <h4 class="fw-bold">Capacity</h4>
                    <p><i class="fa-solid fa-people-group"></i> {{ $space->capacity_min }} - {{ $space->capacity_max }}</p>
                </div>

                <div
                    style="background: #fafafa; border-radius: 8px; padding: 12px; margin-top: 10px;
                        height: 400px; min-height: 320px; overflow-y: auto; display: flex; flex-direction: column;
                        justify-content: flex-start;">
                    <h4 class="fw-bold pt-2 mb-3">Amenities</h4>
                    <ul class="list-unstyled mb-0">
                        <li><strong>Facilities:</strong></li>
                        <ul style="margin-left: 16px;">
                            @forelse ($space->facilities ?? [] as $facility)
                                <li>{{ $facility }}</li>
                            @empty
                                <li>No facilities registered</li>
                            @endforelse
                        </ul>

                        <li class="mt-3"><strong>Description:</strong></li>
                        <ul style="margin-left: 16px;">
                            <li>{!! nl2br(e($space->description)) !!}</li>
                        </ul>
                    </ul>
                </div>
            </div>

            {{-- right --}}
            <div class="col-md-4 pe-3 d-flex flex-column justify-content-between" style="height: 100%;">
                <div>
                    @if (!empty($space->map_embed))
                        <iframe src="https://www.google.com/maps/embed?pb={{ $space->map_embed }}" width="100%"
                            height="250" style="border:0; border-radius:8px;" allowfullscreen="" loading="lazy"></iframe>
                    @endif
                    <p class="map-address small text-muted mb-1">
                        <i class="fa-solid fa-location-dot me-1 text-muted"></i>
                        {{ $space->address ?? 'No address registered' }}
                    </p>
                    @if (!empty($space->address))
                        <a href="https://maps.google.com/?q={{ urlencode($space->address) }}" target="_blank"
                            style="text-decoration: none; color:#547fa1;">view in a map ></a>
                    @endif
                </div>

                <div>
                    <h4 class="fw-bold mb-1">Price</h4>
                    <div style="display: flex; gap: 40px;">
                        <div>
                            <p class="mb-1">weekday :</p>
                            <p style="font-weight: bold; font-size: 1.2rem; color: #333;">
                                ${{ number_format($space->weekday_price, 2) }}/h
                            </p>
                        </div>
                        <div>
                            <p class="mb-1">weekend :</p>
                            <p style="font-weight: bold; font-size: 1.2rem; color: #333;">
                                ${{ number_format($space->weekend_price, 2) }}/h
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('contact.create') }}" style="color:#547fa1; text-decoration:none;"
                        class="mt-4 d-inline-block pb-3">contact us ></a>
                    <a href="{{ route('rooms.reserve.form', ['space_id' => $space->id]) }}" class="btn btn-primary w-100"
                        style="background-color: rgba(84,127,161,1); border: none; color: #fff;">
                        Reserve
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
