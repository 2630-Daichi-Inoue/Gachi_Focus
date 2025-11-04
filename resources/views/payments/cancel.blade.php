@extends('layouts.app')
@section('title','Payment canceled | GachiFocus')
@section('content')
<div class="container py-5 text-center">
  <h1 class="mb-3">Payment canceled</h1>
  <p class="text-muted">You can try again anytime.</p>
  <a href="{{ url()->previous() }}" class="btn btn-outline-dark btn-lg mt-3">Back</a>
</div>
@endsection