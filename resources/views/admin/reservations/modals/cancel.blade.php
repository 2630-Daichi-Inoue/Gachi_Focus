<div class="modal fade"
    id="confirmCancelModal-{{ $reservation->id }}"
    tabindex="-1"
    aria-labelledby="confirmCancelLabel-{{ $reservation->id }}"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header border-danger">
                <h5 class="modal-title text-danger"
                    id="confirmCancelLabel-{{ $reservation->id }}">Cancel Reservation</h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body text-wrap">
                Are you sure you want to cancel <strong>{{ $reservation->user->name }}</strong>'s reservation for <strong>{{ $reservation->space->name }}</strong> ?
                <br>
                This action cannot be undone.
            </div>
            <div class="modal-footer border-0">
                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">No, keep it.
                </button>
                <button type="button"
                        class="btn btn-danger"
                        onclick="document.getElementById('cancel-reservation-form-{{ $reservation->id }}').submit();">
                        Yes, cancel it.
                </button>
            </div>
        </div>
    </div>
</div>
