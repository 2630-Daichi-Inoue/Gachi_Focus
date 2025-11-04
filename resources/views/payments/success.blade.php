@extends('layouts.app')
@section('title','Payment successful | GachiFocus')
@section('content')
<div class="container py-5 text-center">
  <h1 class="mb-3">Thank you!</h1>
  <p class="text-muted">Your payment has been received.</p>
  <a href="{{ route('reservations.current') }}" class="btn btn-dark btn-lg mt-3">View my reservations</a>
</div>
@endsection