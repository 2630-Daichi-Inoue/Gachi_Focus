@extends('layouts.admin')

@section('title', 'Amenity List')

@section('content')
<div class="container-xxl">
  <div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h3 mb-0">Amenity List</h1>
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
          <form class="row gy-2 gx-2 align-items-center" method="POST" action="{{ route('admin.amenities.store') }}">
            @csrf
            <div class="col-12 col-md">
              <input
                type="text"
                name="name"
                value="{{ old('name') }}"
                class="form-control"
                placeholder="Add new amenity"
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
                  <th class="w-75">Amenity</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($amenities as $amenity)
                    <tr>
                        <td class="fs-5">{{ $amenity->name }}</td>
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
                                data-bs-target="#confirmEditModal"
                                data-id="{{ $amenity->id }}"
                                data-name="{{ e($amenity->name) }}"
                                data-action="{{ route('admin.amenities.update', $amenity) }}"
                                >
                                Edit
                                </button>

                                {{-- Delete button: same size --}}
                                <button type="button"
                                        class="btn text-danger px-3 py-2"
                                        style="border:0.5px solid #e27b7b; background-color:transparent; width:90px; transition:0.2s;"
                                        onmouseover="this.style.backgroundColor='#ffeaea';"
                                        onmouseout="this.style.backgroundColor='transparent';"
                                        data-bs-toggle="modal"
                                        data-bs-target="#confirmDeleteModal"
                                        data-id="{{ $amenity->id }}"
                                        data-name="{{ e($amenity->name) }}"
                                        data-action="{{ route('admin.amenities.destroy', $amenity) }}"
                                        >
                                Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-muted py-4">No amenities yet.</td>
                    </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {{-- pagination --}}
      <div class="mt-3">
        {{ $amenities->links() }}
      </div>
    </div>
  </div>
</div>

<form id="delete-amenity-form"
    action=""
    method="POST"
    class="d-none">
    @csrf
    @method('DELETE')
</form>

@include('admin.amenities.modals.edit')
@include('admin.amenities.modals.delete')

@endsection

@section('scripts')
<script>
    // Edit modal handler
        const editModalEl = document.getElementById('confirmEditModal');
        editModalEl?.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const name = button.getAttribute('data-name');
        const action = button.getAttribute('data-action');

        // Set form action and field
            const form = document.getElementById('edit-amenity-form');
            form.action = action;
            document.getElementById('editAmenityName').value = name;
        });

    // delete modal handler
        const deleteModalEl = document.getElementById('confirmDeleteModal');
        deleteModalEl?.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const name = button.getAttribute('data-name');
        const action = button.getAttribute('data-action');

        // Set form action and label
            const form = document.getElementById('delete-amenity-form');
            // form.action = action;
            form.action = button.getAttribute('data-action');
            document.getElementById('deleteAmenityName').textContent = name;
        });
</script>
@endsection
