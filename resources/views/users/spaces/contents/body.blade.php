<div class="card-body border-start border-end border-dark">
    {{-- the picture of the space --}}
    <div class="row mb-2">
        <div class="col-6">
            <img src="{{ $space->image }}" alt="post id {{ $space->id }}" class="w-100" style="height: 100px; object-fit: cover">
        </div>
        <div class="col-6">
            <p class="mb-1">{{ $space->location_for_overview }}</p>
            @php
                $min_price = min($space->weekday_price, $space->weekend_price);
            @endphp
            <p class="mb-1">Fee / h: ¥{{ number_format($min_price) }}〜</p>
            <p class="mb-1">Capacity: {{ $space->min_capacity }} ~ {{ $space->max_capacity }}</p>
            <p class="mb-1">Rating: ★{{ $space->reviews_avg_rating ? number_format($space->reviews_avg_rating, 1) : '-' }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            <button class="bg-white w-100 border border-dark rounded">Check details</button>
        </div>

        <div class="col-6">
            <button class="w-100 fw-bold text-white border border-dark rounded" style="background-color: #757B9D">Book now!</button>
        </div>
    </div>

</div>