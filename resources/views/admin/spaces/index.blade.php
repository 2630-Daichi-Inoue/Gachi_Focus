@extends('layouts.admin')

@section('title', 'Admin: Spaces')

<style>
    .input-unified {
        height: 36px;
    }

    /* Fixed layout + fit width */
    .table-fixed {
        table-layout: fixed;
        width: 100%;
    }

    /* No wrap + ellipsis on selected columns */
    .table-fixed th,
    .table-fixed td {
        white-space: nowrap;
        max-width: 0; /* prevent auto-expansion */
    }

    /* Column widths (sum approx 100%) */
    .table-fixed th:nth-child(1), .table-fixed td:nth-child(1) { width: 20%; }  /* Name */
    .table-fixed th:nth-child(2), .table-fixed td:nth-child(2) { width: 10%; }  /* Prefecture */
    .table-fixed th:nth-child(3), .table-fixed td:nth-child(3) { width: 10%; }  /* City / Ward */
    .table-fixed th:nth-child(4), .table-fixed td:nth-child(4) { width: 30%; }  /* Address Line */
    .table-fixed th:nth-child(5), .table-fixed td:nth-child(5) { width: 20%; }  /* Is Public */
    .table-fixed th:nth-child(6), .table-fixed td:nth-child(6) { width: 10%; }  /* Action */
</style>

@section('content')

    {{-- Flash messages --}}
    @if (session('ok'))
        <div class="alert alert-success alert-dismissible fade show border border-success-subtle" role="alert">
            {{ session('ok') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border border-danger-subtle" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="alert alert-warning alert-dismissible fade show border border-warning-subtle" role="alert">
            <strong>Validation error:</strong>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="GET" action="{{ route('admin.spaces.index') }}" id="searchForm">
        <div class="row mb-2 align-items-stretch">
            <div class="col-md-6">
                <h1 class="h3 mb-0">Space list</h1>
            </div>
            <div class="col-md-6 d-flex gap-5 justify-content-end">
                <!-- Clear button -->
                <a href="{{ route('admin.spaces.index') }}"
                   class="btn btn-outline-secondary bg-secondary-subtle text-dark border  w-25 h-100 d-flex align-items-center justify-content-center">
                    Clear filters
                </a>

                <!-- Submit button-->
                <button type="submit"
                        class="border rounded px-3 py-1 text-white fw-bold w-25 h-100 d-flex align-items-center justify-content-center"
                        style="background-color: #757B9D; letter-spacing: 0.15em;">
                    Search
                </button>
            </div>
        </div>

        <div class="row mb-2 align-items-stretch">
            <!-- Name -->
            <div class="col-md-2">
                <label for="name" class="form-label mb-1 large text-muted">Name</label>
                <div class="position-relative">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-1 text-muted"></i>
                    <input type="search" name="name" id="name"
                        class="form-control form-control-sm border ps-4 input-unified"
                        placeholder="Search by name."
                        value="{{ request('name') }}">
                </div>
            </div>

            <!-- Prefecture -->
            <div class="col-md-2">
                <label for="prefecture" class="form-label mb-1 large text-muted">Prefecture</label>
                <div class="position-relative">
                    <select size="1"
                            id="prefecture"
                            name="prefecture"
                            class="form-control form-control-m border text-dark input-unified">
                        <option value="">All</option>

                        @foreach ($prefectures as $prefecture)
                            <option value="{{ $prefecture }}" {{ request('prefecture') === $prefecture ? 'selected' : '' }}>
                                {{ $prefecture }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- City -->
            <div class="col-md-2">
                <label for="city" class="form-label mb-1 large text-muted">City / Ward</label>
                <div class="position-relative">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-1 text-muted"></i>
                    <input type="text"
                            name="city"
                            id="city"
                            class="form-control form-control-sm border ps-4 input-unified"
                            placeholder="Search by city or ward."
                            value="{{ request('city') }}">
                </div>
            </div>

            <!-- Address Line -->
            <div class="col-md-2">
                <label for="address_line" class="form-label mb-1 large text-muted">Address Line</label>
                <div class="position-relative">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-1 text-muted"></i>
                    <input type="text" name="address_line" id="address_line"
                        class="form-control form-control-sm border ps-4 input-unified"
                        placeholder="Search by address line."
                        value="{{ request('address_line') }}">
                </div>
            </div>

            <!-- Status -->
            <div class="col-md-2">
                <label for="is_public" class="form-label mb-1 large text-muted">Status</label>
                @php $is_public = request('is_public', 'all'); @endphp
                <div class="position-relative">
                    <select name="is_public"
                            id="is_public"
                            class="form-control form-control-m border text-dark input-unified">
                        <option value="all">All</option>
                        <option value="1" {{ $is_public === '1' ? 'selected' : '' }}>Public</option>
                        <option value="0" {{ $is_public === '0' ? 'selected' : '' }}>Hidden</option>
                    </select>
                </div>
            </div>
        </div>
    </form>

    @if ($spaces->isEmpty())
        <div class="text-center">
            <h2>No results.</h2>
            <p class="text-secondary">Try different filters or remove them.</p>
        </div>
    @else
        <table class="table table-hover align-middle bg-white border text-secondary table-fixed">
            <thead class="small table-success text-secondary">
                <tr>
                    <th>Name</th>
                    <th>Prefecture</th>
                    <th>City / Ward</th>
                    <th>Address Line</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($spaces as $space)
                    <tr>
                        {{-- Name --}}
                        <td class="text-truncate">{{ $space->name }}</td>

                        {{-- Prefecture --}}
                        <td class="text-truncate">{{ $space->prefecture }}</td>

                        {{-- City --}}
                        <td class="text-truncate">{{ $space->city }}</td>

                        {{-- Address Line --}}
                        <td class="text-truncate">{{ $space->address_line }}</td>

                        {{-- Status --}}
                        <td class="text-truncate">
                            @if ($space->is_public)
                                <span class="text-dark">Public</span>
                            @else
                                <span class="text-danger">Hidden</span>
                            @endif
                        </td>

                        {{-- Actions (conditions kept; null-safe checks above guard display) --}}
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis"></i>
                                </button>
                                <div class="dropdown-menu">
                                    {{-- Notifications --}}
                                    <button type="button"
                                            class="dropdown-item"
                                            onclick="window.location='{{ route('admin.space-notifications.create', ['space' => $space]) }}'">
                                        <i class="fa-solid fa-bell"></i> Notifications
                                    {{-- Edit --}}
                                    <button type="button"
                                            class="dropdown-item"
                                            onclick="window.location='{{ route('admin.spaces.edit', $space) }}'">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>

                                    {{-- Public -> Hidden --}}
                                    @if ($space->is_public === true)
                                        <button type="button"
                                                class="dropdown-item text-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmHideModal-{{ $space->id }}"
                                                data-mode="hide">
                                            <i class="fa-solid fa-ban"></i> Hide
                                        </button>
                                        <form id="hide-space-form-{{ $space->id }}"
                                                action="{{ route('admin.spaces.hide', $space) }}"
                                                method="POST"
                                                class="d-none">
                                            @csrf
                                            @method('PATCH')
                                        </form>
                                    @endif

                                    {{-- Hidden -> Public --}}
                                    @if ($space->is_public === false)
                                        <button type="button"
                                                class="dropdown-item text-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmShowModal-{{ $space->id }}"
                                                data-mode="show">
                                            <i class="fa-solid fa-arrow-rotate-left"></i> Show
                                        </button>
                                        <form id="show-space-form-{{ $space->id }}"
                                            action="{{ route('admin.spaces.show', $space) }}"
                                            method="POST"
                                            class="d-none">
                                            @csrf
                                            @method('PATCH')
                                        </form>
                                    @endif
                                </div>
                            </div>
                            {{-- Modals --}}
                            @include('admin.spaces.modals.hide', ['space' => $space])
                            @include('admin.spaces.modals.show', ['space' => $space])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- footer: rows per page (instant) + pagination -->
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0">
                    Showing {{ $spaces->firstItem() }} - {{ $spaces->lastItem() }} of
                    {{ $spaces->total() }}
                </p>
            </div>
            <div class="col-md-4">
                <form id="rowsPerPageForm"
                    method="GET"
                    action="{{ route('admin.spaces.index') }}"
                    class="d-flex align-items-center gap-2">
                    <label for="rows_per_page" class="mb-0 small text-muted">Rows per page:</label>
                    @php $per = (int) request('rows_per_page', 20); @endphp
                    <select name="rows_per_page" id="rows_per_page"
                            class="form-select form-select-sm  text-dark w-auto">
                        <option value="20" {{ $per === 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $per === 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $per === 100 ? 'selected' : '' }}>100</option>
                    </select>

                    <!-- keep current filters when changing page size -->
                    <input type="hidden" name="name" value="{{ request('name') }}">
                    <input type="hidden" name="prefecture" value="{{ request('prefecture') }}">
                    <input type="hidden" name="city" value="{{ request('city') }}">
                    <input type="hidden" name="address_line" value="{{ request('address_line') }}">
                    <input type="hidden" name="is_public" value="{{ request('is_public', 'all') }}">
                </form>
            </div>
            <div class="col-md-2 d-flex justify-content-end">
                {{-- {{ $spaces->withQueryString()->links() }} --}}
                {{ $spaces->withQueryString()->links() }}
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    <!-- instant apply JS for rows_per_page -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const perSel = document.getElementById('rows_per_page');
            const perForm = document.getElementById('rowsPerPageForm');
            perSel?.addEventListener('change', () => perForm?.submit());
        });
    </script>
@endsection
