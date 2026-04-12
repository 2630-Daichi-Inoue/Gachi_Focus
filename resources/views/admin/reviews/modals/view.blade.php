<div class="modal fade"
    id="viewModal-{{ $review->id }}"
    tabindex="-1"
    aria-labelledby="viewLabel-{{ $review->id }}"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-primary">
            <div class="modal-header border-primary">
                <h5 class="modal-title text-wrap"
                    id="viewLabel-{{ $review->id }}">
                    User: {{ $review->user->name }}
                    <br>
                    Space: {{ $review->reservation->space->name }}
                </h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body text-wrap">
                {{ $review->comment }}
            </div>
        </div>
    </div>
</div>
