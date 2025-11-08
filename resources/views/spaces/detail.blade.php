@extends('layouts.app')

@section('title', $space->name . ' | Coworking Space Detail')

@section('content')

    {{-- left --}}
    <div id="photo-column"
        style="position: fixed; top: 0; left: 0; bottom: 0; width: 31.5%; overflow-y: auto; background: #fff;
        display: flex; flex-direction: column; gap: 14px; padding-left: 1rem; box-sizing: border-box; z-index: 1;">
        @forelse ($space->photos as $photo)
            <img src="{{ asset('storage/' . $photo->path) }}" alt="Coworking photo"
                style="width: 100%; height: 260px; object-fit: cover;
        border-radius: 0.3rem;
        transition: transform 0.3s ease; cursor: pointer;"
                onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
        @empty
            <div style="display:flex; align-items:center; justify-content:center; height:100vh; text-align:center;">
                <p style="color:#999; font-style:italic; font-size:1rem;">
                    No photos available for this coworking space.
                </p>
            </div>
        @endforelse
    </div>

    {{-- auto scroll --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('photo-column');
            const photos = Array.from(container.querySelectorAll('img'));
            if (photos.length > 2) {
                const clone = photos.map(img => img.cloneNode(true));
                clone.forEach(c => container.appendChild(c));
                container.addEventListener('scroll', () => {
                    if (container.scrollTop + container.clientHeight >= container.scrollHeight - 1) {
                        container.scrollTop = 0;
                    }
                });
            }
        });
    </script>

    <div class="container-fluid py-4"
        style="height: 100vh; overflow: hidden; position: fixed; top: 95px; right: 0; left: 32.5%;
           padding-left: 36px;">
        <div class="row" style="display: flex; align-items: stretch; height: calc(100vh - 150px); overflow: hidden;">

            {{-- middle --}}
            <div class="col-md-4"
                style="padding-left: 8px; display: flex; flex-direction: column; justify-content: flex-start;">
                <div style="margin-left: 0; margin-right: 12px;">
                    <div
                        style="
                            background: #ffffff; 
                            border: 1px solid rgba(0,0,0,0.06);
                            border-radius: 12px;
                            box-shadow: 0 2px 6px rgba(0,0,0,0.05); 
                            padding: 24px 26px;
                            margin-bottom: 32px;
                        ">
                        <h1 class="fw-bold mb-2" style="font-size:1.6rem; color:#222;">
                            {{ $space->name }}
                        </h1>

                        @php
                            $rating = round($space->rating ?? 0, 1);
                            $fullStars = floor($rating);
                            $halfStar = $rating - $fullStars >= 0.5 ? 1 : 0;
                            $emptyStars = 5 - $fullStars - $halfStar;
                        @endphp

                        @if ($reviewCount > 0)
                            <div class="d-flex align-items-center mb-2">
                                @for ($i = 0; $i < $fullStars; $i++)
                                    <i class="fa-solid fa-star text-warning"></i>
                                @endfor
                                @if ($halfStar)
                                    <i class="fa-solid fa-star-half-stroke text-warning"></i>
                                @endif
                                @for ($i = 0; $i < $emptyStars; $i++)
                                    <i class="fa-regular fa-star text-warning"></i>
                                @endfor
                                <span style="color:#555; font-size:0.95rem; margin-left:6px;">
                                    {{ $rating }} / 5
                                </span>
                                <a href="{{ route('reviews.index', $space->id) }}"
                                    style="color:#547fa1; text-decoration:none; margin-left:8px;">
                                    reviews >
                                </a>
                            </div>
                        @else
                            <p style="color:#888; font-style:italic; margin-bottom:10px;">No reviews yet</p>
                        @endif
                    </div>

                    <div style="margin-left: 20px; margin-right: 10px;">
                        <h4 class="fw-bold" style="margin-top: 30px;">Capacity</h4>
                        <p><i class="fa-solid fa-people-group"></i> {{ $space->capacity_min }} - {{ $space->capacity_max }}
                        </p>
                    </div>

                    <div style="padding: 12px; margin-top: -10px; overflow-y: auto; margin-right: 8px;">
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
            </div>

            {{-- right --}}
            <div class="col-md-4 pe-3 d-flex flex-column justify-content-between" style="height: 100%;">
                {{-- Map Section --}}
                <div style="margin-top: 0; margin-bottom: 20px;">
                    @if (!empty($space->map_embed))
                        <div
                            style="width: 95%; height: 300px; border-radius: 10px; overflow: hidden;
                                margin: 0 auto; box-shadow: 0 3px 8px rgba(0,0,0,0.08);">
                            <iframe src="https://www.google.com/maps/embed?pb={{ $space->map_embed }}" width="100%"
                                height="100%" style="border:0;" allowfullscreen="" loading="lazy">
                            </iframe>
                        </div>
                    @endif

                    <div style="width: 95%; margin: 8px auto 0;">
                        <p class="map-address small text-muted mb-1">
                            <i class="fa-solid fa-location-dot me-1 text-muted"></i>
                            {{ $space->address ?? 'No address registered' }}
                        </p>
                        @if (!empty($space->address))
                            <a href="https://maps.google.com/?q={{ urlencode($space->address) }}" target="_blank"
                                style="text-decoration: none; color:#547fa1;">view in a map ></a>
                        @endif
                    </div>
                </div>

                {{-- PRICE CARD --}}
                <div style="width: 95%; margin: 0 auto;">
                    <h4 class="fw-bold mb-3">Price</h4>

                    <div style="display: flex; gap: 18px; justify-content: space-between;">
                        {{-- Weekday Box --}}
                        <div
                            style="
                                flex: 1;
                                background: #fafafa;
                                border: 1px solid rgba(0,0,0,0.05);
                                border-radius: 8px;
                                padding: 14px 10px;
                                text-align: center;
                            ">
                            <p class="mb-1 text-secondary small fw-semibold">weekday</p>
                            <p style="font-weight: 700; font-size: 1.3rem; color: #333; margin-bottom: 0;">
                                ${{ number_format($space->weekday_price, 2) }}/h
                            </p>
                        </div>

                        {{-- Weekend Box --}}
                        <div
                            style="
                                flex: 1;
                                background: #fafafa;
                                border: 1px solid rgba(0,0,0,0.05);
                                border-radius: 8px;
                                padding: 14px 10px;
                                text-align: center;
                            ">
                            <p class="mb-1 text-secondary small fw-semibold">weekend</p>
                            <p style="font-weight: 700; font-size: 1.3rem; color: #333; margin-bottom: 0;">
                                ${{ number_format($space->weekend_price, 2) }}/h
                            </p>
                        </div>
                    </div>

                    <div class="d-flex flex-column align-items-start mt-4" style="margin-left: 6px;">
                        <a href="{{ route('contact.create') }}" class="text-decoration-none" style="color:#547fa1;">
                            contact us >
                        </a>

                        <a href="{{ route('rooms.reserve.form', ['space_id' => $space->id]) }}" class="btn mt-3"
                            style="
                                background-color: rgba(84,127,161,1);
                                border: none;
                                color: #fff;
                                font-size: 1.05rem;
                                padding: 7px 0;
                                border-radius: 6px;
                                width: 95%;
                                align-self: flex-start;
                                margin-left: 0;
                            ">
                            Reserve
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
