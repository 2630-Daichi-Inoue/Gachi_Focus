@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">Space Info</div>
                <div class="card-body">
                    <h4>{{ $space->name }}</h4>
                    <p>{{ $space->address }}</p>
                    <p>Type: {{ $space->type }}</p>
                    <p>Capacity: {{ $space->capacity }} people</p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Facilities</div>
                <div class="card-body">
                    <ul>
                        @foreach($space->facilities as $facility)
                            <li>{{ $facility->name }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="d-grid">
                <a href="{{ route('reservation.create', $space->id) }}" class="btn btn-primary btn-lg">Reserve</a>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">Links</div>
                <div class="card-body">
                    <p>
                       
                        <a href="{{ route('spaces.reviews.index', $space->id) }}">reviews</a>
                    </p>
                    <p>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($space->address) }}" target="_blank" rel="noopener noreferrer">
                            view in a map >
                        </a>
                    </p>
                    <p>
                        <a href="{{ route('contact') }}">Contact us</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection