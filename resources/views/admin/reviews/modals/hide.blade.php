<div class="modal fade"
    id="confirmHideModal-{{ $review->id }}"
    tabindex="-1"
    aria-labelledby="confirmHideLabel-{{ $review->id }}"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header border-danger">
                <h5 class="modal-title text-danger"
                    id="confirmHideLabel-{{ $review->id }}">Hide Review</h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body text-wrap">
                Are you sure you want to hide <strong>{{ $review->user->name }}'s review</strong> ?
                <br>
                You can show it again later if needed.
            </div>
            <div class="modal-footer border-0">
                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">No, keep it.
                </button>
                <button type="button"
                        class="btn btn-danger"
                        onclick="document.getElementById('hide-review-form-{{ $review->id }}').submit();">
                        Yes, hide it.
                </button>
            </div>
        </div>
    </div>
</div>
