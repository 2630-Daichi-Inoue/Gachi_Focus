{{-- Edit Modal --}}
<div class="modal fade"
    id="confirmEditModal"
    tabindex="-1"
    aria-labelledby="confirmEditModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="edit-amenity-form"
            action=""
            method="POST"
            class="modal-content">
            @csrf
            @method('PATCH')
            <div class="modal-header">
                <h5 class="modal-title" id="confirmEditModalLabel">Edit amenity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="editAmenityName" class="form-label">Name</label>
                    <input type="text" name="name" id="editAmenityName" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Edit</button>
            </div>
        </form>
     </div>
</div>
