@extends('layouts.app')

@section('title', 'Admin: Reservations')

@section('content')
    <form method="GET"
            action="{{ route('admin.reservations') }}"
            id="searchForm">
        <div class="row mb-2 align-items-stretch">
            <div class="col-md-6">
                <h2>Reservation list</h2>
            </div>
            <div class="col-md-6 d-flex gap-5 justify-content-end">
                <!-- Clear button -->
                <a href="{{ route('admin.reservations') }}"
                    class="btn btn-outline-secondary bg-secondary-subtle text-dark border border-dark w-25 h-100">
                    Clear filters
                </a>

                <!-- Submit button-->
                <button type="submit"
                        class="border border-dark rounded px-3 py-1 text-white fw-bold w-25 h-100"
                        style="background-color: #757B9D; letter-spacing: 0.15em;">
                    Search
                </button>
            </div>
        </div>

        <div class="row mb-2 align-items-stretch">

            <!-- name -->
            <div class="col-md-2">
                <label for="name" class="form-label mb-1 small text-muted">Name</label>
                <div class="position-relative">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-1 text-muted"></i>
                    <input type="search"
                            name="name"
                            id="name"
                            class="form-control form-control-sm border border-dark ps-4"
                            placeholder="Search by name."
                            value="{{ request('name') }}">
                </div>
            </div>

            <!-- space -->
            <div class="col-md-2">
                <label for="space" class="form-label mb-1 small text-muted">Space</label>
                <div class="position-relative">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-1 text-muted"></i>
                    <input type="search"
                            name="space"
                            id="space"
                            class="form-control form-control-sm border border-dark ps-4"
                            placeholder="Search by space."
                            value="{{ request('space') }}">
                </div>
            </div>

            <!-- date(from) -->
            <div class="col-md-2">
                <label for="date_from" class="form-label small text-muted mb-1">Date(from)</label>
                <input type="date"
                        name="date_from"
                        id="date_from"
                        class="form-control form-control-sm border border-dark"
                        value="{{ request('date_from') }}">
            </div>

            <!-- date(to) -->
            <div class="col-md-2">
                <label for="date_to" class="form-label small text-muted mb-1">Date(to)</label>
                <input type="date"
                        name="date_to"
                        id="date_to"
                        class="form-control form-control-sm border border-dark"
                        value="{{ request('date_to') }}">
            </div>

            <!-- status (instant apply) -->
            <div class="col-md-2">
                <label for="status" class="form-label mb-1 small text-muted">Status</label>
                @php
                    $status = request('status', 'all');
                @endphp
                <select name="status"
                        id="status"
                        class="form-select form-select-sm border border-dark text-dark">
                    <option value="all" {{ $status==='all' ? 'selected' : '' }}>All</option>
                    <option value="active" {{ $status==='active' ? 'selected' : '' }}>Active</option>
                    <option value="cancelled" {{ $status==='cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="completed" {{ $status==='completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

            <!-- payment (instant apply) -->
            <div class="col-md-2">
                <label for="payment" class="form-label mb-1 small text-muted">Payment</label>
                @php
                    $payment = request('payment', 'all');
                @endphp
                <select name="payment"
                        id="payment"
                        class="form-select form-select-sm border border-dark text-dark">
                    <option value="all" {{ $payment==='all' ? 'selected' : '' }}>All</option>
                    <option value="paid" {{ $payment==='paid' ? 'selected' : '' }}>Paid</option>
                    <option value="unpaid" {{ $payment==='unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="refunded" {{ $payment==='refunded' ? 'selected' : '' }}>Refunded</option>
                    <option value="refund_pending" {{ $payment==='refund_pending' ? 'selected' : '' }}>Refund Pending</option>
                </select>
            </div>

        </div>
    </form>

    <table class="table table-hover align-middle bg-white border text-secondary">
        <thead class="small table-success text-secondary">
            <tr>
                <th>Reserv. ID</th>
                <th>Name</th>
                <th>Space</th>
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
                    <td>
                        <a href="{{ route('profile.show', $reservation->user->id) }}"
                            class="text-decoration-none text-dark">{{ $reservation->user->name }}</a>
                    </td>
                    <td>{{ $reservation->space->name }}</td>
                    <td>{{ $reservation->start_time }}</td>
                    <td>{{ $reservation->end_time }}</td>
                    <td>{{ $reservation->total_price }}</td>
                    <td>
                        @php
                            $statusMap = \App\Models\Reservation::STATUS_MAP[$reservation->status] ?? ['icon'=>'', 'class'=>'badge bg-light']
                        @endphp
                        <span class="{{ $statusMap['class'] }}">
                            @if($statusMap['icon'])
                                <i class="{{ $statusMap['icon'] }}"></i>
                            @endif
                            {{ $reservation->status }}
                        </span>
                    </td>
                    <td>
                        @php
                            $paymentStatus = optional($reservation->payment)->status;

                            $paymentMap = $paymentStatus ? (\App\Models\Reservation::PAYMENT_MAP[$paymentStatus] ?? ['icon' => '', 'class' => 'badge bg-secondary text-white rounded-pill fw-light']) : ['icon' => '', 'class' => 'badge bg-secondary text-white rounded-pill fw-light'];
                        @endphp

                        <span class="{{ $paymentMap['class'] }}">
                            @if($paymentMap['icon'])
                                <i class="{{ $paymentMap['icon'] }}"></i>
                            @endif
                            {{ $paymentStatus ?? '—' }}
                        </span>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm" data-bs-toggle="dropdown"><i class="fas fa-ellipsis"></i></button>
                            <div class="dropdown-menu">
                                @if ($reservation->status === 'Active')
                                    <button class="dropdown-item">
                                        <i class="fa-solid fa-eye"></i> View
                                    </button>
                                    <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#cancel-reservation-{{ $reservation->id }}">
                                        <i class="fa-solid fa-ban"></i> Cancel
                                    </button>
                                @elseif ($reservation->payment->status === 'Refund Pending')
                                    <button class="dropdown-item">
                                        <i class="fa-solid fa-eye"></i> View
                                    </button>
                                    <button class="dropdown-item text-primary">
                                        <i class="fa-solid fa-arrow-rotate-left"></i> Refund
                                    </button>
                                @else
                                    <button class="dropdown-item">
                                        <i class="fa-solid fa-eye"></i> View
                                    </button>
                                @endif
                            </div>
                        </div>
                        {{-- include modal here --}}

                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- footer: rows per page (instant) + pagination -->
    <div class="row align-items-center">
        <div class="col-md-6">
            @if ($all_reservations->isNotEmpty())
                <p class="mb-0">
                    Showing {{ $all_reservations->firstItem() }} - {{ $all_reservations->lastItem()}} of {{ $all_reservations->total() }}
                </p>
            @endif
        </div>
        <div class="col-md-4">
            <form id="rowsPerPageForm"
                    method="GET"
                    action="{{ route('admin.reservations') }}"
                    class="d-flex align-items center gap-2 justify-content-end">
                <label for="rows_per_page" class="mb-0 small text-muted">Rows per page:</label>
                @php
                    $per = (int)request('rows_per_page', 20);
                @endphp
                <select name="rows_per_page"
                        id="rows_per_page"
                        class="form-select form-select-sm border-dark text-dark w-auto">
                    <option value="20" {{ $per===20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $per===50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $per===100 ? 'selected' : '' }}>100</option>
                </select>

                <!-- keep current filters when changing page size -->
                <input type="hidden" name="name" value="{{ request('name') }}">
                <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                <input type="hidden" name="date_to"   value="{{ request('date_to') }}">
                <input type="hidden" name="status" value="{{ request('status', 'all') }}">
            </form>
        </div>

        <div class="col-md-2 d-flex justify-content-end">
            {{ $all_reservations->withQueryString()->links() }}
        </div>
    </div>

    <!-- instant apply JS for statu & rows_per_page -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const statusSel = document.getElementById('status');
            statusSel?.addEventListener('change', () => statusSel.form?.submit());

            const perSel = document.getElementById('rows_per_page');
            const perForm = document.getElementById('rowsPerPageForm');
            perSel?.addEventListener('change', () => perForm?.submit());
        });
    </script>
@endsection