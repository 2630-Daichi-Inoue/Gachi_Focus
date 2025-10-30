@php
  // required: $reservation, $modalId
  $title = $title ?? 'Cancel Reservation';
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0">
      <div class="modal-header bg-light">
        <h5 class="modal-title fw-semibold" id="{{ $modalId }}Label">{{ $title }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <p class="mb-0">Are you sure you want to cancel this reservation?</p>
      </div>

      <div class="modal-footer">
        <form method="POST" action="{{ route('reservations.cancel', ['id' => $reservation->id]) }}">
          @csrf
          <button type="submit" class="btn btn-danger">Yes, cancel</button>
        </form>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>