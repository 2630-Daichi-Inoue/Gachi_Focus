@extends('layouts.admin')

@section('title', 'Admin: Users')

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

    /* Column widths */
    .table-fixed th:nth-child(1), .table-fixed td:nth-child(1) { width: 20%; } /* Name */
    .table-fixed th:nth-child(2), .table-fixed td:nth-child(2) { width: 20%; } /* Email */
    .table-fixed th:nth-child(3), .table-fixed td:nth-child(3) { width: 20%; } /* Phone Number */
    .table-fixed th:nth-child(4), .table-fixed td:nth-child(4) { width: 10%; } /* Status */
    .table-fixed th:nth-child(5), .table-fixed td:nth-child(5) { width: 20%; } /* Registered At */
    .table-fixed th:nth-child(6), .table-fixed td:nth-child(6) { width: 5%; }  /* Action */
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

    <form method="GET" action="{{ route('admin.users.index') }}" id="searchForm">
        <div class="row mb-2 align-items-stretch">
            <div class="col-md-6">
                <h1 class="h3 mb-0">User list</h1>
            </div>
            <div class="col-md-6 d-flex gap-5 justify-content-end">
                <!-- Clear button -->
                <a href="{{ route('admin.users.index') }}"
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
                <label for="name" class="form-label mb-1 small text-muted">Name</label>
                <div class="position-relative">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-1 text-muted"></i>
                    <input type="search"
                           name="name"
                           id="name"
                           class="form-control form-control-sm border input-unified ps-4"
                           placeholder="Search by user name."
                           value="{{ request('name') }}">
                </div>
            </div>

            <!-- Email -->
            <div class="col-md-2">
                <label for="email" class="form-label mb-1 small text-muted">Email</label>
                <div class="position-relative">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-1 text-muted"></i>
                    <input type="search"
                           name="email"
                           id="email"
                           class="form-control form-control-sm border input-unified ps-4"
                           placeholder="Search by user email."
                           value="{{ request('email') }}">
                </div>
            </div>

            <!-- Status -->
            <div class="col-md-2">
                <label for="status" class="form-label mb-1 small text-muted">
                    Status
                </label>
                @php $status = request('status', 'all'); @endphp
                <select name="status" id="status" class="form-control form-control-m border text-dark input-unified">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="restricted" {{ $status === 'restricted' ? 'selected' : '' }}>Restricted</option>
                    <option value="banned" {{ $status === 'banned' ? 'selected' : '' }}>Banned</option>
                </select>
            </div>

        </div>
    </form>

    @if ($users->isEmpty())
        <div class="text-center">
            <h2>No results.</h2>
            <p class="text-secondary">Try different filters or remove them.</p>
        </div>
    @else
        <table class="table table-hover align-middle bg-white border text-secondary table-fixed">
            <thead class="small table-success text-secondary">
                <tr>
                    <th>User Name</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Status</th>
                    <th>Registered At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        {{-- User (null-safe) --}}
                        <td class="text-truncate">{{ $user->name }}</td>

                        {{-- Email --}}
                        <td>{{ $user->email }}</td>

                        {{-- Phone --}}
                        <td>{{ $user->phone ?? '-' }}</td>

                        {{-- Status --}}
                        <td>
                            @if($user->deleted_at !== null)
                                <span class="text-secondary">Deleted</span>
                            @elseif($user->user_status === 'active')
                                <span class="text-dark">Active</span>
                            @elseif($user->user_status === 'restricted')
                                <span class="text-warning">Restricted</span>
                            @elseif($user->user_status === 'banned')
                                <span class="text-danger">Banned</span>
                            @endif
                        </td>

                        {{-- Registered At --}}
                        <td>{{ $user->created_at->format('Y/n/j G:i') }}</td>

                        {{-- Actions (conditions kept; null-safe checks above guard display) --}}
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis"></i>
                                </button>
                                <div class="dropdown-menu">
                                    {{-- View --}}
                                    {{-- <button type="button" class="dropdown-item">
                                        <i class="fa-solid fa-eye"></i> View
                                    </button> --}}

                                    {{-- Active or Banned -> Restricted --}}
                                    @if($user->user_status === 'active' || $user->user_status === 'banned')
                                        <button type="button"
                                                class="dropdown-item text-warning"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmRestrictModal-{{ $user->id }}"
                                                data-mode="restrict">
                                            <i class="fa-solid fa-ban"></i> Restrict
                                        </button>
                                        <form id="restrict-user-form-{{ $user->id }}"
                                                action="{{ route('admin.users.restrict', $user) }}"
                                                method="POST"
                                                class="d-none">
                                            @csrf
                                            @method('PATCH')
                                        </form>
                                    @elseif($user->user_status === 'restricted')
                                        <button type="button"
                                                class="dropdown-item text-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmActivateModal-{{ $user->id }}"
                                                data-mode="activate">
                                            <i class="fa-solid fa-arrow-rotate-left"></i> Activate
                                        </button>
                                        <form id="activate-user-form-{{ $user->id }}"
                                                action="{{ route('admin.users.activate', $user) }}"
                                                method="POST"
                                                class="d-none">
                                            @csrf
                                            @method('PATCH')
                                        </form>
                                        <button type="button"
                                                class="dropdown-item text-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmBanModal-{{ $user->id }}"
                                                data-mode="ban">
                                            <i class="fa-solid fa-ban"></i> Ban
                                        </button>
                                        <form id="ban-user-form-{{ $user->id }}"
                                                action="{{ route('admin.users.ban', $user) }}"
                                                method="POST"
                                                class="d-none">
                                            @csrf
                                            @method('PATCH')
                                        </form>
                                    @endif
                                </div>
                            </div>

                            {{-- Modal --}}
                            @include('admin.users.modals.restrict', ['user' => $user])
                            @include('admin.users.modals.activate', ['user' => $user])
                            @include('admin.users.modals.ban', ['user' => $user])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- footer: rows per page (instant) + pagination -->
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0">
                    Showing {{ $users->firstItem() }} - {{ $users->lastItem() }} of
                    {{ $users->total() }}
                </p>
            </div>
            <div class="col-md-4">
                <form id="rowsPerPageForm"
                    method="GET"
                    action="{{ route('admin.users.index') }}"
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
                    <input type="hidden" name="email" value="{{ request('email') }}">
                    <input type="hidden" name="status" value="{{ request('status', 'all') }}">
                </form>
            </div>
            <div class="col-md-2 d-flex justify-content-end">
                {{ $users->withQueryString()->links() }}
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    <script>
        // instant apply JS for rows_per_page
        document.addEventListener('DOMContentLoaded', () => {
            const perSel = document.getElementById('rows_per_page');
            const perForm = document.getElementById('rowsPerPageForm');
            perSel?.addEventListener('change', () => perForm?.submit());
        });
    </script>
@endsection
