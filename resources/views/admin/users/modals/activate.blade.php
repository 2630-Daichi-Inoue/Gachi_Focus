<div class="modal fade"
    id="confirmActivateModal-{{ $user->id }}"
    tabindex="-1"
    aria-labelledby="confirmActivateLabel-{{ $user->id }}"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header border-danger">
                <h5 class="modal-title text-danger"
                    id="confirmActivateLabel-{{ $user->id }}">Activate User</h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body text-wrap">
                Are you sure you want to activate <strong>{{ $user->name }}</strong> ?
                <br>
                You can restrict them again later if needed.
            </div>
            <div class="modal-footer border-0">
                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">No, keep them.
                </button>
                <button type="button"
                        class="btn btn-danger"
                        onclick="document.getElementById('activate-user-form-{{ $user->id }}').submit();">
                        Yes, activate them.
                </button>
            </div>
        </div>
    </div>
</div>
