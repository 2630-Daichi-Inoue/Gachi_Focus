<div class="modal fade"
    id="viewModal-{{ $announcement->id }}"
    tabindex="-1"
    aria-labelledby="viewLabel-{{ $announcement->id }}"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-primary">
            <div class="modal-header border-primary">
                <h5 class="modal-title text-wrap"
                    id="viewLabel-{{ $announcement->id }}">
                    {{ $announcement->title }}
                </h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>
            </div>
            <div class="modal-body text-wrap">
                {{ $announcement->message }}
            </div>
        </div>
    </div>
</div>
