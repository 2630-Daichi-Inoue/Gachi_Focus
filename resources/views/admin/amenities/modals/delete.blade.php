{{-- Delete Modal --}}
<div class="modal fade"
    id="confirmDeleteModal"
    tabindex="-1"
    aria-labelledby="confirmDeleteLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="delete-amenity-form"
            action=""
            method="POST"
            class="modal-content">
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteLabel">Delete amenity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
           <div class="modal-body">
                Are you sure you want to delete <strong id="deleteAmenityName"></strong>?
            </div>
            <div class="modal-footer border-0">
                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">No, cancel.
                </button>
                <button type="button"
                        class="btn btn-danger"
                        onclick="document.getElementById('delete-amenity-form').submit();">
                        Yes, delete.
                </button>
            </div>
        </form>
     </div>
</div>
