<div class="modal fade"
    id="confirmDeleteModal"
    tabindex="-1"
    aria-labelledby="confirmDeleteLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header border-danger">
                <h5 class="modal-title text-danger"
                    id="confirmDeleteLabel">Delete Space</h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body text-wrap">
                Are you sure you want to delete <strong>{{ $space->name }}</strong>?
                <br>
                This action cannot be undone and the space will no longer be available.
            </div>
            <div class="modal-footer border-0">
                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">No, cancel.
                </button>
                <button type="button"
                        class="btn btn-danger"
                        onclick="document.getElementById('delete-space-form').submit();">
                        Yes, delete.
                </button>
            </div>
        </div>
    </div>
</div>
