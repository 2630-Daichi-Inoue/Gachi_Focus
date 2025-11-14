@extends('layouts.app')

@section('title', 'Admin: Reservations')

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
                <h2>Reservation list</h2>
            </div>
            <div class="col-md-6 d-flex gap-5 justify-content-end">
                <!-- Clear button -->
                <a href="{{ route('admin.reservations.index') }}"
                   class="btn btn-outline-secondary bg-secondary-subtle text-dark border  w-25 h-100 d-flex align-items-center justify-content-center">
                    Clear filters
                </a>

                <!-- Submit button-->
                <button type="submit"
                        class="border  rounded px-3 py-1 text-white fw-bold w-25 h-100 d-flex align-items-center justify-content-center"
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
                    <input type="search" name="user_name" id="user_name"
                           class="form-control form-control-sm border  ps-4"
                           placeholder="Search by user name."
                           value="{{ request('user_name') }}">
                </div>
            </div>

            <!-- space -->
            <div class="col-md-2">
                <label for="space_name" class="form-label mb-1 small text-muted">Space name</label>
                <div class="position-relative">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-1 text-muted"></i>
                    <input type="search" name="space_name" id="space_name"
                           class="form-control form-control-sm border  ps-4"
                           placeholder="Search by space name."
                           value="{{ request('space_name') }}">
                </div>
            </div>

            <!-- date(from) -->
            <div class="col-md-2">
                <label for="date_from" class="form-label small text-muted mb-1">Date(from)</label>
                <input type="date" name="date_from" id="date_from"
                       class="form-control form-control-sm border "
                       value="{{ request('date_from') }}">
            </div>

            <!-- date(to) -->
            <div class="col-md-2">
                <label for="date_to" class="form-label small text-muted mb-1">Date(to)</label>
                <input type="date" name="date_to" id="date_to"
                       class="form-control form-control-sm border "
                       value="{{ request('date_to') }}">
            </div>

            <!-- status (instant apply) -->
            <div class="col-md-2">
                <label for="status" class="form-label mb-1 small text-muted">Status</label>
                @php $status = request('status', 'all'); @endphp
                <select name="status" id="status" class="form-select form-select-sm border  text-dark">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                    <option value="Active" {{ $status === 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Cancelled" {{ $status === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="Completed" {{ $status === 'Completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

            <!-- payment (instant apply) -->
            <div class="col-md-2">
                <label for="payment" class="form-label mb-1 small text-muted">Payment</label>
                @php $payment = request('payment', 'all'); @endphp
                <select name="payment" id="payment" class="form-select form-select-sm border  text-dark">
                    <option value="all" {{ $payment === 'all' ? 'selected' : '' }}>All</option>
                    <option value="Paid" {{ $payment === 'Paid' ? 'selected' : '' }}>Paid</option>
                    <option value="Unpaid" {{ $payment === 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="Refunded" {{ $payment === 'Refunded' ? 'selected' : '' }}>Refunded</option>
                    <option value="Refund Pending" {{ $payment === 'Refund Pending' ? 'selected' : '' }}>Refund Pending</option>
                </select>
            </div>
        </div>
    </form>

    @if ($all_reservations->isEmpty())
        <div class="text-center">
            <h2>No results.</h2>
            <p class="text-secondary">Try different filters or remove them.</p>
        </div>
    @else
        <table class="table table-hover align-middle bg-white border text-secondary table-fixed">
            <thead class="small table-success text-secondary">
                <tr>
                    <th>Reserv. ID</th>
                    <th>User Name</th>
                    <th>Space Name</th>
                    <th>Starting Time</th>
                    <th>Ending Time</th>
                    <th>Fee</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($all_reservations as $reservation)
                    <tr>
                        <td>{{ $reservation->id }}</td>

                        {{-- User (null-safe) --}}
                        <td>
                            @if ($reservation->user)
                                <a href="{{ route('profile.show', $reservation->user->id) }}"
                                   class="text-decoration-none text-dark">
                                    {{ $reservation->user->name }}
                                </a>
                            @else
                                <span class="text-muted">[Deleted user #{{ $reservation->user_id }}]</span>
                            @endif
                        </td>

                        {{-- Space (null-safe) --}}
                        <td>
                            @if ($reservation->space)
                                {{ $reservation->space->name }}
                            @else
                                <span class="text-muted">[Missing space #{{ $reservation->space_id }}]</span>
                            @endif
                        </td>

                        {{-- Start / End (safe formatting) --}}
                        <td>{{ \Carbon\Carbon::parse($reservation->start_time)->format('Y/n/j G:i') }}</td>
                        <td>{{ \Carbon\Carbon::parse($reservation->end_time)->format('Y/n/j G:i') }}</td>

                        {{-- Fee (keep as-is to avoid logic impact) --}}
                        <td>{{ $reservation->total_price }}</td>

                        {{-- Booking status (use simple badge; avoid assuming icon/class map) --}}
                        <td>
                            @php
                                // Map internal status -> label; fallback to raw text or dash
                                $statusLabel = \App\Models\Reservation::STATUS_MAP[$reservation->status] ?? ($reservation->status ?? '—');
                            @endphp
                            <span class="badge bg-light text-dark border">{{ $statusLabel }}</span>
                        </td>

                        {{-- Payment (UI map preserved; prefer Payment relation status, then reservation->payment_status) --}}
                        <td>
                            @php
                                // Prefer Payment relation's status if present (e.g., 'Paid', 'Refund Pending', etc.)
                                $paymentStatusLabel = optional($reservation->payment)->status;

                                // If relation missing, build label from reservation->payment_status ('paid' etc.)
                                if (!$paymentStatusLabel) {
                                    $paymentStatusLabel = $reservation->displayStatusLabel(); // returns 'Paid'/'Unpaid'/'Refunded'
                                }

                                // Badge style/icon from UI map (kept compatible)
                                $pui = \App\Models\Reservation::PAYMENT_UI_MAP[$paymentStatusLabel]
                                    ?? ['icon' => '', 'class' => 'badge bg-secondary text-white rounded-pill fw-light'];
                            @endphp

                            <span class="{{ $pui['class'] }}">
                                @if (!empty($pui['icon']))
                                    <i class="{{ $pui['icon'] }}"></i>
                                @endif
                                {{ $paymentStatusLabel ?? '—' }}
                            </span>
                        </td>

                        {{-- Actions (conditions kept; null-safe checks above guard display) --}}
                        <td>
                            @php
                                $isActive = strtolower((string) $reservation->status) === 'active';
                                $isRefundPending = optional($reservation->payment)->status === 'Refund Pending';
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

                                    {{-- Active -> Cancel --}}
                                    @if ($isActive)
                                        <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal"
                                                data-bs-target="#reservation-action-{{ $reservation->id }}"
                                                data-mode="cancel">
                                            <i class="fa-solid fa-ban"></i> Cancel
                                        </button>
                                    @endif

                                    {{-- Refund Pending -> Refund --}}
                                    @if ($isRefundPending)
                                        <button type="button" class="dropdown-item text-primary" data-bs-toggle="modal"
                                                data-bs-target="#reservation-action-{{ $reservation->id }}"
                                                data-mode="refund">
                                            <i class="fa-solid fa-arrow-rotate-left"></i> Refund
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
                    Showing {{ $all_reservations->firstItem() }} - {{ $all_reservations->lastItem() }} of
                    {{ $all_reservations->total() }}
                </p>
            </div>
            <div class="col-md-4">
                <form id="rowsPerPageForm" method="GET" action="{{ route('admin.reservations.index') }}"
                      class="d-flex align-items center gap-2 justify-content-end">
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
                    <input type="hidden" name="payment" value="{{ request('payment', 'all') }}">
                </form>
            </div>
            <div class="col-md-2 d-flex justify-content-end">
                {{ $all_reservations->withQueryString()->links() }}
            </div>
        </div>
    @endif

    <!-- instant apply JS for status & payment & rows_per_page -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const statusSel = document.getElementById('status');
            statusSel?.addEventListener('change', () => statusSel.form?.submit());

            const paymentSel = document.getElementById('payment');
            paymentSel?.addEventListener('change', () => paymentSel.form?.submit());

            const perSel = document.getElementById('rows_per_page');
            const perForm = document.getElementById('rowsPerPageForm');
            perSel?.addEventListener('change', () => perForm?.submit());
        });
    </script>

    <style>
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
@endsection
