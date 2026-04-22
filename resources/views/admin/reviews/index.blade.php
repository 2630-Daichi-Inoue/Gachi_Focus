@extends('layouts.admin')

@section('title', 'Admin: Reviews')

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
    .table-fixed th:nth-child(2), .table-fixed td:nth-child(2) { width: 15%; }  /* Space name */
    .table-fixed th:nth-child(3), .table-fixed td:nth-child(3) { width: 5%; }  /* Rating */
    .table-fixed th:nth-child(4), .table-fixed td:nth-child(4) { width: 30%; }  /* Comment */
    .table-fixed th:nth-child(5), .table-fixed td:nth-child(5) { width: 10%; }  /* Status */
    .table-fixed th:nth-child(6), .table-fixed td:nth-child(6) { width: 15%; }  /* Created At */
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

    <form method="GET" action="{{ route('admin.reviews.index') }}" id="searchForm">
        <div class="row mb-2 align-items-stretch">
            <div class="col-md-6">
                <h1 class="h3 mb-0">Review list</h1>
            </div>
            <div class="col-md-6 d-flex gap-5 justify-content-end">
                <!-- Clear button -->
                <a href="{{ route('admin.reviews.index') }}"
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

            <!-- Space name -->
            <div class="col-md-2">
                <label for="space_name" class="form-label mb-1 large text-muted">Space name</label>
                <div class="position-relative">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-1 text-muted"></i>
                    <input type="search" name="space_name" id="space_name"
                        class="form-control form-control-sm border ps-4 input-unified"
                        placeholder="Search by space name."
                        value="{{ request('space_name') }}">
                </div>
            </div>

            <!-- Rating -->
            <div class="col-md-2">
                <label for="rating" class="form-label mb-1 large text-muted">Rating</label>
                @php $rating = request('rating', 'all'); @endphp
                <div class="position-relative">
                    <select name="rating"
                            id="rating"
                            class="form-control form-control-m border text-dark input-unified">
                        <option value="all">All</option>
                        <option value="5" {{ $rating === '5' ? 'selected' : '' }}>5</option>
                        <option value="4" {{ $rating === '4' ? 'selected' : '' }}>4</option>
                        <option value="3" {{ $rating === '3' ? 'selected' : '' }}>3</option>
                        <option value="2" {{ $rating === '2' ? 'selected' : '' }}>2</option>
                        <option value="1" {{ $rating === '1' ? 'selected' : '' }}>1</option>
                    </select>
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

            <!-- Status -->
            <div class="col-md-2">
                <label for="is_public" class="form-label mb-1 large text-muted">Status</label>
                @php $isPublic = request('is_public', 'all'); @endphp
                <div class="position-relative">
                    <select name="is_public"
                            id="is_public"
                            class="form-control form-control-m border text-dark input-unified">
                        <option value="all">All</option>
                        <option value="1" {{ $isPublic === '1' ? 'selected' : '' }}>Public</option>
                        <option value="0" {{ $isPublic === '0' ? 'selected' : '' }}>Hidden</option>
                    </select>
                </div>
            </div>
        </div>
    </form>

    @if ($reviews->isEmpty())
        <div class="text-center">
            <h2>No results.</h2>
            <p class="text-secondary">Try different filters or remove them.</p>
        </div>
    @else
        <table class="table table-hover align-middle bg-white border text-secondary table-fixed">
            <thead class="small table-success text-secondary">
                <tr>
                    <th>Username</th>
                    <th>Space Name</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reviews as $review)
                    <tr>
                        {{-- Username --}}
                        <td class="text-truncate">{{ $review->user->name }}</td>

                        {{-- Space name --}}
                        <td class="text-truncate">{{ $review->reservation->space->name }}</td>

                        {{-- Rating --}}
                        <td class="text-truncate">{{ $review->rating }}</td>

                        {{-- Comment --}}
                        <td class="text-truncate">{{ $review->comment }}</td>

                        {{-- Status --}}
                        <td class="text-truncate">
                            @if ($review->is_public)
                                <span class="text-dark">Public</span>
                            @else
                                <span class="text-danger">Hidden</span>
                            @endif
                        </td>

                        {{-- Created At --}}
                        <td>{{ $review->created_at->format('Y/n/j G:i') }}</td>

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
                                            data-bs-target="#viewModal-{{ $review->id }}">
                                        <i class="fa-solid fa-eye"></i> View Full Comment
                                    </button>

                                    {{-- Public -> Hidden --}}
                                    @if ($review->is_public === true)
                                        <button type="button"
                                                class="dropdown-item text-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmHideModal-{{ $review->id }}"
                                                data-mode="hide">
                                            <i class="fa-solid fa-ban"></i> Hide
                                        </button>
                                        <form id="hide-review-form-{{ $review->id }}"
                                                action="{{ route('admin.reviews.hide', $review) }}"
                                                method="POST"
                                                class="d-none">
                                            @csrf
                                            @method('PATCH')
                                        </form>
                                    @endif

                                    {{-- Hidden -> Public --}}
                                    @if ($review->is_public === false)
                                        <button type="button"
                                                class="dropdown-item text-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmShowModal-{{ $review->id }}"
                                                data-mode="show">
                                            <i class="fa-solid fa-arrow-rotate-left"></i> Show
                                        </button>
                                        <form id="show-review-form-{{ $review->id }}"
                                            action="{{ route('admin.reviews.show', $review) }}"
                                            method="POST"
                                            class="d-none">
                                            @csrf
                                            @method('PATCH')
                                        </form>
                                    @endif
                                </div>
                            </div>
                            {{-- Modals --}}
                            @include('admin.reviews.modals.view', ['review' => $review])
                            @include('admin.reviews.modals.hide', ['review' => $review])
                            @include('admin.reviews.modals.show', ['review' => $review])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- footer: rows per page (instant) + pagination -->
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0">
                    Showing {{ $reviews->firstItem() }} - {{ $reviews->lastItem() }} of
                    {{ $reviews->total() }}
                </p>
            </div>
            <div class="col-md-4">
                <form id="rowsPerPageForm"
                    method="GET"
                    action="{{ route('admin.reviews.index') }}"
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
                    <input type="hidden" name="space_name" value="{{ request('space_name') }}">
                    <input type="hidden" name="rating" value="{{ request('rating', 'all') }}">
                    <input type="hidden" name="keyword" value="{{ request('keyword') }}">
                    <input type="hidden" name="is_public" value="{{ request('is_public', 'all') }}">
                </form>
            </div>
            <div class="col-md-2 d-flex justify-content-end">
                {{-- {{ $reviews->withQueryString()->links() }} --}}
                {{ $reviews->withQueryString()->links() }}
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
