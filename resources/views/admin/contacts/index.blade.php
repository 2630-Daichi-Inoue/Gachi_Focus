@extends('layouts.admin')

@section('title', 'Admin: Contacts')

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
    .table-fixed th:nth-child(3), .table-fixed td:nth-child(3) { width: 15%; }  /* Reservation */
    .table-fixed th:nth-child(4), .table-fixed td:nth-child(4) { width: 10%; }  /* Status */
    .table-fixed th:nth-child(5), .table-fixed td:nth-child(5) { width: 10%; }  /* Created At */
    .table-fixed th:nth-child(6), .table-fixed td:nth-child(6) { width: 10%; }  /* Read At */
    .table-fixed th:nth-child(7), .table-fixed td:nth-child(7) { width: 10%; }  /* Action */
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

    <form method="GET" action="{{ route('admin.contacts.index') }}" id="searchForm">
        <div class="row mb-2 align-items-stretch">
            <div class="col-md-6">
                <h1 class="h3 mb-0">Contact list</h1>
            </div>
            <div class="col-md-6 d-flex gap-5 justify-content-end">
                <!-- Clear button -->
                <a href="{{ route('admin.contacts.index') }}"
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
            <!-- Username -->
            <div class="col-md-2">
                <label for="user_name" class="form-label mb-1 large text-muted">Username</label>
                <div class="position-relative">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-1 text-muted"></i>
                    <input type="search" name="user_name" id="user_name"
                        class="form-control form-control-sm border ps-4 input-unified"
                        placeholder="Search by username."
                        value="{{ request('user_name') }}">
                </div>
            </div>

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

            <!-- Contact Status -->
            <div class="col-md-2">
                <label for="contact_status" class="form-label mb-1 large text-muted">Contact Status</label>
                @php $contactStatus = request('contact_status', 'all'); @endphp
                <div class="position-relative">
                    <select name="contact_status"
                            id="contact_status"
                            class="form-control form-control-m border text-dark input-unified">
                        <option value="all">All</option>
                        <option value="open" {{ $contactStatus === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="closed" {{ $contactStatus === 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="canceled" {{ $contactStatus === 'canceled' ? 'selected' : '' }}>Canceled</option>
                    </select>
                </div>
            </div>

            <!-- Read Status -->
            <div class="col-md-2">
                <label for="read_status" class="form-label mb-1 large text-muted">Read Status</label>
                @php $readStatus = request('read_status', 'all'); @endphp
                <div class="position-relative">
                    <select name="read_status"
                            id="read_status"
                            class="form-control form-control-m border text-dark input-unified">
                        <option value="all">All</option>
                        <option value="1" {{ $readStatus === '1' ? 'selected' : '' }}>Read</option>
                        <option value="0" {{ $readStatus === '0' ? 'selected' : '' }}>Unread</option>
                    </select>
                </div>
            </div>
        </div>
    </form>

    @if ($contacts->isEmpty())
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
                    <th>Reservation</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Read At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($contacts as $contact)
                    <tr>
                        {{-- Username --}}
                        <td class="text-truncate">{{ $contact->user->name }}</td>

                        {{-- Title --}}
                        <td class="text-truncate">{{ $contact->title }}</td>

                        {{-- Reservation --}}
                        <td class="text-truncate">
                            @if ($contact->reservation)
                                <a href="{{ route('admin.reservations.index', ['reservation_id' => $contact->reservation->id]) }}">
                                    View Reservation
                                </a>
                            @else
                                -
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="text-truncate">
                            @if ($contact->contact_status === 'open')
                                <span class="text-dark">Open</span>
                            @elseif ($contact->contact_status === 'closed')
                                <span class="text-success">Closed</span>
                            @elseif ($contact->contact_status === 'canceled')
                                <span class="text-danger">Canceled</span>
                            @endif
                        </td>

                        {{-- Created At --}}
                        <td>{{ $contact->created_at->format('Y/n/j G:i') }}</td>

                        {{-- Read At --}}
                        <td>{{ $contact->read_at ? $contact->read_at->format('Y/n/j G:i') : 'Unread' }}</td>

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
                                            data-bs-target="#viewModal-{{ $contact->id }}">
                                        <i class="fa-solid fa-eye"></i> View Full Message
                                    </button>
                                    <form id="read-contact-form-{{ $contact->id }}"
                                            action="{{ route('admin.contacts.read', $contact) }}"
                                            method="POST"
                                            class="d-none">
                                        @csrf
                                        @method('PATCH')
                                    </form>
                                    <form id="close-contact-form-{{ $contact->id }}"
                                            action="{{ route('admin.contacts.close', $contact) }}"
                                            method="POST"
                                            class="d-none"
                                            >
                                        @csrf
                                        @method('PATCH')
                                    </form>
                                </div>
                            </div>
                            {{-- Modals --}}
                            @include('admin.contacts.modals.view', ['contact' => $contact])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- footer: rows per page (instant) + pagination -->
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0">
                    Showing {{ $contacts->firstItem() }} - {{ $contacts->lastItem() }} of
                    {{ $contacts->total() }}
                </p>
            </div>
            <div class="col-md-4">
                <form id="rowsPerPageForm"
                    method="GET"
                    action="{{ route('admin.contacts.index') }}"
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
                    <input type="hidden" name="user_name" value="{{ request('user_name') }}">
                    <input type="hidden" name="keyword" value="{{ request('keyword') }}">
                    <input type="hidden" name="contact_status" value="{{ request('contact_status', 'all') }}">
                    <input type="hidden" name="read_status" value="{{ request('read_status', 'all') }}">
                </form>
            </div>
            <div class="col-md-2 d-flex justify-content-end">
                {{ $contacts->withQueryString()->links() }}
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
