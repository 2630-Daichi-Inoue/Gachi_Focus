@extends('layouts.admin')

@section('title', 'Admin: Reservations')

<style>
    .input-unified {
        height: 36px;
    }
    /* Fixed layout + fit width */
    .table-fixed { table-layout: fixed; width: 100%; }

    /* No wrap + ellipsis on selected columns */
    .table-fixed th, .table-fixed td { white-space: nowrap; }
    .table-fixed td:nth-child(2),
    .table-fixed td:nth-child(3),
    .table-fixed td:nth-child(4),
    .table-fixed td:nth-child(5) {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Column widths (sum approx 100%) */
    .table-fixed th:nth-child(1), .table-fixed td:nth-child(1) { width: 6%; }   /* ID */
    .table-fixed th:nth-child(2), .table-fixed td:nth-child(2) { width: 12%; }  /* User */
    .table-fixed th:nth-child(3), .table-fixed td:nth-child(3) { width: 18%; }  /* Space */
    .table-fixed th:nth-child(4), .table-fixed td:nth-child(4) { width: 14%; }  /* Start */
    .table-fixed th:nth-child(5), .table-fixed td:nth-child(5) { width: 14%; }  /* End */
    .table-fixed th:nth-child(6), .table-fixed td:nth-child(6) { width: 8%; }   /* Fee */
    .table-fixed th:nth-child(7), .table-fixed td:nth-child(7) { width: 10%; }  /* Status */
    .table-fixed th:nth-child(8), .table-fixed td:nth-child(8) { width: 14%; }  /* Payment */
    .table-fixed th:nth-child(9), .table-fixed td:nth-child(9) { width: 4%; }   /* Action */
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

    <form method="GET" action="{{ route('admin.reservations.index') }}" id="searchForm">
        <div class="row mb-2 align-items-stretch">
            <div class="col-md-6">
                <h1>Reservation list</h1>
            </div>
            <div class="col-md-6 d-flex gap-5 justify-content-end">
                <!-- Clear button -->
                <a href="{{ route('admin.reservations.index') }}"
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
            <!-- name -->
            <div class="col-md-2">
                <label for="user_name" class="form-label mb-1 small text-muted">User name</label>
                <div class="position-relative">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-1 text-muted"></i>
                    <input type="search"
                           name="user_name"
                           id="user_name"
                           class="form-control form-control-sm border input-unified ps-4"
                           placeholder="Search by user name."
                           value="{{ request('user_name') }}">
                    {{-- <button type="button"
                            id="clearUserName"
                            class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-1 p-0 bg-transparent border-0 text-muted">
                        <i class="fa-solid fa-xmark"></i>
                    </button> --}}
                </div>
            </div>

            <!-- space -->
            <div class="col-md-2">
                <label for="space_name" class="form-label mb-1 small text-muted">Space name</label>
                <div class="position-relative">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-1 text-muted"></i>
                    <input type="search"
                            name="space_name"
                            id="space_name"
                            class="form-control form-control-sm border input-unified ps-4"
                            placeholder="Search by space name."
                            value="{{ request('space_name') }}">
                    {{-- <button type="button"
                            id="clearSpaceName"
                            class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-1 p-0 bg-transparent border-0 text-muted">
                        <i class="fa-solid fa-xmark"></i>
                    </button> --}}
                </div>
            </div>

            <!-- date(from) -->
            <div class="col-md-2">
                <label for="date_from" class="form-label small text-muted mb-1">Date(from)</label>
                <input type="date"
                        name="date_from"
                        id="date_from"
                        class="form-control form-control-sm border input-unified"
                        value="{{ request('date_from') }}">
            </div>

            <!-- date(to) -->
            <div class="col-md-2">
                <label for="date_to" class="form-label small text-muted mb-1">Date(to)</label>
                <input type="date"
                        name="date_to"
                        id="date_to"
                        class="form-control form-control-sm border input-unified"
                        value="{{ request('date_to') }}">
            </div>

            <!-- status (instant apply) -->
            <div class="col-md-2">
                <label for="status" class="form-label mb-1 small text-muted">
                    Status
                </label>
                @php $status = request('status', 'all'); @endphp
                <select name="status" id="status" class="form-control form-control-m border text-dark input-unified">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                    <option value="booked" {{ $status === 'booked' ? 'selected' : '' }}>Booked</option>
                    <option value="canceled" {{ $status === 'canceled' ? 'selected' : '' }}>Canceled</option>
                </select>
            </div>

        </div>
    </form>

    @if ($reservations->isEmpty())
        <div class="text-center">
            <h2>No results.</h2>
            <p class="text-secondary">Try different filters or remove them.</p>
        </div>
    @else
        <table class="table table-hover align-middle bg-white border text-secondary table-fixed">
            <thead class="small table-success text-secondary">
                <tr>
                    <th style="width: 15%;">User Name</th>
                    <th style="width: 20%;">Space Name</th>
                    <th style="width: 15%;">Start At</th>
                    <th style="width: 15%;">End At</th>
                    <th style="width: 10%;">Quantity</th>
                    <th style="width: 10%;">Total Price</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 5%;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reservations as $reservation)
                    <tr>
                        {{-- User (null-safe) --}}
                        <td class="text-truncate">
                            @if ($reservation->user)
                                <p>{{ $reservation->user->name }}</p>
                                {{-- <a href="{{ route('profile.show', $reservation->user->id) }}"
                                   class="text-decoration-none text-dark">
                                    {{ $reservation->user->name }}
                                </a> --}}
                            @else
                                <span class="text-muted">[Deleted user: {{ $reservation->user->name }}]</span>
                            @endif
                        </td>

                        {{-- Space (null-safe) --}}
                        <td class="text-truncate">
                            @if ($reservation->space)
                                {{ $reservation->space->name }}
                            @else
                                <span class="text-muted">[Missing space: {{ $reservation->space->name }}]</span>
                            @endif
                        </td>

                        {{-- Start / End (safe formatting) --}}
                        <td>{{ \Carbon\Carbon::parse($reservation->start_at)->format('Y/n/j G:i') }}</td>
                        <td>{{ \Carbon\Carbon::parse($reservation->end_at)->format('Y/n/j G:i') }}</td>

                        {{-- Quantity --}}
                        <td>{{ $reservation->quantity }}</td>

                        {{-- Total Price (keep as-is to avoid logic impact) --}}
                        <td>¥ {{ number_format($reservation->total_price_yen) }}</td>

                        {{-- Booking status (use simple badge; avoid assuming icon/class map) --}}
                        <td>
                            @php
                                // Map internal status -> label; fallback to raw text or dash
                                $statusLabel = \App\Models\Reservation::RESERVATION_STATUS_MAP[$reservation->reservation_status] ?? ($reservation->reservation_status ?? '—');
                            @endphp
                            <span class="badge bg-light text-dark border">{{ $statusLabel }}</span>
                        </td>

                        {{-- Actions (conditions kept; null-safe checks above guard display) --}}
                        <td>
                            @php
                                $isBooked = strtolower((string) $reservation->reservation_status) === 'booked';
                            @endphp
                            <div class="dropdown">
                                <button class="btn btn-sm" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis"></i>
                                </button>
                                <div class="dropdown-menu">
                                    {{-- View --}}
                                    <button type="button" class="dropdown-item">
                                        <i class="fa-solid fa-eye"></i> View
                                    </button>

                                    {{-- Booked -> Cancel --}}
                                    @if ($isBooked)
                                        <button type="button"
                                                class="dropdown-item text-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#reservation-action-{{ $reservation->id }}"
                                                data-mode="cancel">
                                            <i class="fa-solid fa-ban"></i> Cancel
                                        </button>
                                    @endif
                                </div>
                            </div>

                            {{-- Modal include (unchanged) --}}
                            @include('admin.reservations.modals.action', ['reservation' => $reservation])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- footer: rows per page (instant) + pagination -->
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0">
                    Showing {{ $reservations->firstItem() }} - {{ $reservations->lastItem() }} of
                    {{ $reservations->total() }}
                </p>
            </div>
            <div class="col-md-4">
                <form id="rowsPerPageForm"
                    method="GET"
                    action="{{ route('admin.reservations.index') }}"
                    class="d-flex align-items center gap-2">
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
                    <input type="hidden" name="space_name" value="{{ request('space_name') }}">
                    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                    <input type="hidden" name="status" value="{{ request('status', 'all') }}">
                </form>
            </div>
            <div class="col-md-2 d-flex justify-content-end">
                {{ $reservations->withQueryString()->links() }}
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

        // instant clear JS for user_name and space_name
        // document.addEventListener('DOMContentLoaded', () => {
        //     const input = document.getElementById('user_name');
        //     input.value = '';
        //     input.focus();
        // });

    </script>
@endsection
