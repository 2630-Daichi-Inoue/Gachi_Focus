<div class="modal fade"
    id="confirmShowModal-{{ $space->id }}"
    tabindex="-1"
    aria-labelledby="confirmShowLabel-{{ $space->id }}"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header border-danger">
                <h5 class="modal-title text-danger"
                    id="confirmShowLabel-{{ $space->id }}">Show</h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to show <strong>{{ $space->name }}</strong>?
            </div>
            <div class="modal-footer border-0">
                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">No, cancel.
                </button>
                <button type="button"
                        class="btn btn-danger"
                        onclick="document.getElementById('show-space-form-{{ $space->id }}').submit();">
                        Yes, show.
                </button>
            </div>
        </div>
    </div>
</div>
