{{-- hide --}}
<div class="modal fade" id="hide-space-{{ $space->id }}">
    <div class="modal-dialog">
        <div class="modal-content border-danger">
            <div class="modal-header border-danger">
                <h3 class="h5 modal-title text-danger">
                    <i class="fas fa-ban"></i> Hide the space
                </h3>
            </div>
            <div class="modal-body">
                Are you sure you want to hide <span class="fw-bold">{{ $space->name }}</span>?
            </div>
            <div class="modal-footer border-0">
                <form action="{{ route('admin.spaces.hide', $space->id) }}" method="post">
                    @csrf
                    @method('PATCH')

                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">No, cancel.</button>
                    <button type="submit" class="btn btn-danger btn-sm">Yes, hide.</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- show --}}
<div class="modal fade" id="show-space-{{ $space->id }}">
    <div class="modal-dialog">
        <div class="modal-content border-success">
            <div class="modal-header border-success">
                <h3 class="h5 modal-title text-success">
                    <i class="fas fa-arrow-rotate-left"></i> Show the space
                </h3>
            </div>
            <div class="modal-body">
                Are you sure you want to show <span class="fw-bold">{{ $space->name }}</span>?
            </div>
            <div class="modal-footer border-0">
                <form action="{{ route('admin.spaces.show', $space->id) }}" method="post">
                    @csrf
                    @method('PATCH')

                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">No, cancel.</button>
                    <button type="submit" class="btn btn-success btn-sm">Yes, show.</button>
                </form>
            </div>
        </div>
    </div>
</div>
