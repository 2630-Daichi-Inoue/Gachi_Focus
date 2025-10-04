<div class="modal fade" id="delete-user-{{ $user->id }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h3 class="fw-bold text-center m-3">Are you sure want to delete your account?</h3>
            </div>
            <div class="modal-footer border-0 d-flex justify-content-center gap-5">
                <div class="flex-fill" style="max-width:150px;">
                    <button class="btn btn-modal-color btn-sm w-100" data-bs-dismiss="modal">Not now</button>
                </div>    

                <div class="flex-fill" style="max-width:150px;">
                    <form action="{{ route('profile.destroy', $user->id) }}" method="post">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-danger btn-sm w-100">Yes</button>
                    </form>
                </div> 
            </div>
        </div>
    </div>
</div>