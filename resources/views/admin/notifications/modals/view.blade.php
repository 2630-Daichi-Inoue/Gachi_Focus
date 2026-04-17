<div class="modal fade"
    id="viewModal-{{ $notification->id }}"
    tabindex="-1"
    aria-labelledby="viewLabel-{{ $notification->id }}"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-primary">
            <div class="modal-header border-primary">
                <h5 class="modal-title text-wrap"
                    id="viewLabel-{{ $notification->id }}">
                    {{ $notification->title }}
                </h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>
            </div>
            <div class="modal-body text-wrap">
                {{ $notification->message }}
            </div>
        </div>
    </div>
</div>
