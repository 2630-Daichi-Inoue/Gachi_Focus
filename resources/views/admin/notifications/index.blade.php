@extends('layouts.admin')

@section('title', 'Admin: Notifications')

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
    .table-fixed th:nth-child(1), .table-fixed td:nth-child(1) { width: 15%; }  /* Username */
    .table-fixed th:nth-child(2), .table-fixed td:nth-child(2) { width: 20%; }  /* Title */
    .table-fixed th:nth-child(3), .table-fixed td:nth-child(3) { width: 20%; }  /* Message */
    .table-fixed th:nth-child(4), .table-fixed td:nth-child(4) { width: 10%; }  /* Type */
    .table-fixed th:nth-child(5), .table-fixed td:nth-child(5) { width: 15%; }  /* Created At */
    .table-fixed th:nth-child(6), .table-fixed td:nth-child(6) { width: 15%; }  /* Read At */
    .table-fixed th:nth-child(7), .table-fixed td:nth-child(7) { width: 5%; }  /* Action */
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

    <form method="GET" action="{{ route('admin.notifications.index') }}" id="searchForm">
        <div class="row mb-2 align-items-stretch">
            <div class="col-md-6">
                <h1 class="h3 mb-0">Notification list</h1>
            </div>
            <div class="col-md-6 d-flex gap-5 justify-content-end">
                <!-- Clear button -->
                <a href="{{ route('admin.notifications.index') }}"
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
            <!-- Keyword -->
            <div class="col-md-2">
                <label for="keyword" class="form-label mb-1 large text-muted">Keyword</label>
                <div class="position-relative">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-1 text-muted"></i>
                    <input type="search" name="keyword" id="keyword"
                        class="form-control form-control-sm border ps-4 input-unified"
                        placeholder="Search by keyword."
                        value="{{ request('keyword') }}">
                </div>
            </div>

            <!-- Type-->
            <div class="col-md-2">
                <label for="type" class="form-label mb-1 large text-muted">Type</label>
                @php $type = request('type', 'all'); @endphp
                <div class="position-relative">
                    <select name="type"
                            id="type"
                            class="form-control form-control-m border text-dark input-unified">
                        <option value="all" {{ $type === 'all' ? 'selected' : '' }}>All</option>
                        <option value="user" {{ $type === 'user' ? 'selected' : '' }}>User</option>
                        <option value="space" {{ $type === 'space' ? 'selected' : '' }}>Space</option>
                        <option value="contact" {{ $type === 'contact' ? 'selected' : '' }}>Contact</option>
                    </select>
                </div>
            </div>

            <!-- Read Status -->
            <div class="col-md-2">
                <label for="readStatus" class="form-label mb-1 large text-muted">Read Status</label>
                @php $readStatus = request('readStatus', 'all'); @endphp
                <div class="position-relative">
                    <select name="readStatus"
                            id="readStatus"
                            class="form-control form-control-m border text-dark input-unified">
                        <option value="all" {{ $readStatus === 'all' ? 'selected' : '' }}>All</option>
                        <option value="1" {{ $readStatus === '1' ? 'selected' : '' }}>Read</option>
                        <option value="0" {{ $readStatus === '0' ? 'selected' : '' }}>Unread</option>
                    </select>
                </div>
            </div>

        </div>
    </form>

    @if ($notifications->isEmpty())
        <div class="text-center">
            <h2>No results.</h2>
            <p class="text-secondary">Try different filters or remove them.</p>
        </div>
    @else
        <table class="table table-hover align-middle bg-white border text-secondary table-fixed">
            <thead class="small table-success text-secondary">
                <tr>
                    <th>Username</th>
                    <th>Title</th>
                    <th>Message</th>
                    <th>Type</th>
                    <th>Created At</th>
                    <th>Read At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($notifications as $notification)
                    <tr>
                        {{-- Username --}}
                        <td class="text-truncate">
                            <a href="{{ route('admin.users.index', ['user_id' => $notification->user->id]) }}">
                                {{ $notification->user->name }}
                            </a>
                        </td>

                        {{-- Title --}}
                        <td class="text-truncate">{{ $notification->title }}</td>

                        {{-- Message --}}
                        <td class="text-truncate">
                           {{ $notification->message }}
                        </td>

                        {{-- Type --}}
                        <td class="text-truncate">
                            @if ($notification->related_type === 'user')
                                <span>User</span>
                            @elseif ($notification->related_type === 'space')
                                <span>Space</span>
                            @elseif ($notification->related_type === 'contact')
                                <span>Contact</span>
                            @endif
                        </td>

                        {{-- Created At --}}
                        <td>{{ $notification->created_at->format('Y/n/j G:i') }}</td>

                        {{-- Read At --}}
                        <td>{{ $notification->read_at ? $notification->read_at->format('Y/n/j G:i') : 'Unread' }}</td>

                        {{-- Actions (conditions kept; null-safe checks above guard display) --}}
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis"></i>
                                </button>
                                <div class="dropdown-menu">
                                    {{-- View --}}
                                    <button type="button"
                                            class="dropdown-item"
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewModal-{{ $notification->id }}">
                                        <i class="fa-solid fa-eye"></i> View Full Message
                                    </button>
                                </div>
                            </div>
                            {{-- Modals --}}
                            @include('admin.notifications.modals.view', ['notification' => $notification])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- footer: rows per page (instant) + pagination -->
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0">
                    Showing {{ $notifications->firstItem() }} - {{ $notifications->lastItem() }} of
                    {{ $notifications->total() }}
                </p>
            </div>
            <div class="col-md-4">
                <form id="rowsPerPageForm"
                    method="GET"
                    action="{{ route('admin.notifications.index') }}"
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
                    <input type="hidden" name="userName" value="{{ request('userName') }}">
                    <input type="hidden" name="spaceName" value="{{ request('spaceName') }}">
                    <input type="hidden" name="rating" value="{{ request('rating', 'all') }}">
                    <input type="hidden" name="keyword" value="{{ request('keyword') }}">
                    <input type="hidden" name="isPublic" value="{{ request('isPublic', 'all') }}">
                </form>
            </div>
            <div class="col-md-2 d-flex justify-content-end">
                {{ $notifications->withQueryString()->links() }}
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
