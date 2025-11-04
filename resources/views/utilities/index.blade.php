@extends('layouts.app')

@section('title', 'Category List')

@section('content')
<div class="container-xxl">
  <div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h3 mb-0">Category List</h1>
      </div>

      {{-- flash success --}}
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      {{-- validation summary (optional) --}}
      @if ($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach ($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- add form --}}
      <div class="card mb-4 border-0 shadow-sm rounded-4">
        <div class="card-body">
          <form class="row gy-2 gx-2 align-items-center" method="POST" action="{{ route('utilities.store') }}">
            @csrf
            <div class="col-12 col-md">
              <input
                type="text"
                name="name"
                value="{{ old('name') }}"
                class="form-control"
                placeholder="Add Utility"
                required
              >
            </div>
            <div class="col-12 col-md-auto">
              {{-- gray button per your preference --}}
              <button type="submit" class="btn btn-dark px-4">
                + add
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- list table --}}
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="w-75">Category</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($utilities as $u)
                  <tr>
                    <td class="fs-5">{{ $u->name }}</td>
                    <td>
                        <div class="d-flex justify-content-center gap-2">
                            {{-- Edit button: deeper blue --}}
                            <button
                            type="button"
                            class="btn text-white px-3 py-2"
                            style="background-color:#858788; border:none; width:90px; transition:0.2s;"
                            onmouseover="this.style.backgroundColor='#428bca';"  
                            onmouseout="this.style.backgroundColor='#858788';"   
                            data-bs-toggle="modal"
                            data-bs-target="#editModal"
                            data-id="{{ $u->id }}"
                            data-name="{{ e($u->name) }}"
                            >
                            Edit
                            </button>


                            {{-- Delete button: same size --}}
                            <button
                            type="button"
                            class="btn text-danger px-3 py-2"
                            style="border:0.5px solid #e27b7b; background-color:transparent; width:90px; transition:0.2s;"
                            onmouseover="this.style.backgroundColor='#ffeaea';"
                            onmouseout="this.style.backgroundColor='transparent';"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteModal"
                            data-id="{{ $u->id }}"
                            data-name="{{ e($u->name) }}"
                            >
                            Delete
                            </button>
                        </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="2" class="text-muted py-4">No tags yet.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {{-- pagination --}}
      <div class="mt-3">
        {{ $utilities->links() }}
      </div>
    </div>
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editForm" method="POST" class="modal-content">
      @csrf
      @method('PUT')
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Edit category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        {{-- populated via JS --}}
        <div class="mb-3">
          <label class="form-label">Name</label>
          <input type="text" name="name" id="editName" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Edit</button>
      </div>
    </form>
  </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="deleteForm" method="POST" class="modal-content">
      @csrf
      @method('DELETE')
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">Delete category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0">Are you sure you want to delete <strong id="deleteName"></strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger">Delete</button>
      </div>
    </form>
  </div>
</div>

{{-- Minimal JS: populate modal forms (no Alpine needed) --}}
<script>
  // Util: build RESTful resource URL
  function utilityUrl(id) {
    // Ensure same as your route('utilities.update', id) / route('utilities.destroy', id)
    return "{{ url('/utilities') }}/" + id;
  }

  // Edit modal handler
  const editModalEl = document.getElementById('editModal');
  editModalEl.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');

    // Set form action and field
    const form = document.getElementById('editForm');
    form.action = utilityUrl(id);
    document.getElementById('editName').value = name;
  });

  // Delete modal handler
  const deleteModalEl = document.getElementById('deleteModal');
  deleteModalEl.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');

    // Set form action and label
    const form = document.getElementById('deleteForm');
    form.action = utilityUrl(id);
    document.getElementById('deleteName').textContent = name;
  });
</script>
@endsection
