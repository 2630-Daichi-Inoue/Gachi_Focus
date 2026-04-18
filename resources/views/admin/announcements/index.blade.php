@extends('layouts.admin')

@section('title', 'Admin: Announcements')

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
    .table-fixed th:nth-child(1), .table-fixed td:nth-child(1) { width: 15%; }  /* Title */
    .table-fixed th:nth-child(2), .table-fixed td:nth-child(2) { width: 35%; }  /* Message */
    .table-fixed th:nth-child(3), .table-fixed td:nth-child(3) { width: 15%; }  /* Published At */
    .table-fixed th:nth-child(4), .table-fixed td:nth-child(4) { width: 15%; }  /* Expired At */
    .table-fixed th:nth-child(5), .table-fixed td:nth-child(5) { width: 10%; }  /* Is Public */
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

    <form method="GET" action="{{ route('admin.announcements.index') }}" id="searchForm">
        <div class="row mb-2 align-items-stretch">
            <div class="col-md-6">
                <h1 class="h3 mb-0">Announcement list</h1>
            </div>
            <div class="col-md-6 d-flex gap-5 justify-content-end">
                <!-- Clear button -->
                <a href="{{ route('admin.announcements.index') }}"
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

            <!-- Publishing Date(from) -->
            <div class="col-md-2">
                <label for="published_date" class="form-label mb-1 large text-muted">Published Date(from)</label>
                <div class="position-relative">
                    <input type="date"
                            name="published_date"
                            id="published_date"
                            class="form-control form-control-sm border input-unified"
                            value="{{ request('published_date') }}">
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

    @if ($announcements->isEmpty())
        <div class="text-center">
            <h2>No results.</h2>
            <p class="text-secondary">Try different filters or remove them.</p>
        </div>
    @else
        <table class="table table-hover align-middle bg-white border text-secondary table-fixed">
            <thead class="small table-success text-secondary">
                <tr>
                    <th>Title</th>
                    <th>Message</th>
                    <th>Published At</th>
                    <th>Expired At</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($announcements as $announcement)
                    <tr>
                        {{-- Title --}}
                        <td class="text-truncate">{{ $announcement->title }}</td>

                        {{-- Message --}}
                        <td class="text-truncate">{{ $announcement->message }}</td>

                        {{-- Published At --}}
                        <td class="text-truncate">
                            {{ \Carbon\Carbon::parse($announcement->published_at)->format('Y/n/j G:i') }}
                        </td>

                        {{-- Expired At --}}
                        <td class="text-truncate">
                            {{ \Carbon\Carbon::parse($announcement->expired_at)->format('Y/n/j G:i') }}
                        </td>

                        {{-- Status --}}
                        <td class="text-truncate">
                            @if ($announcement->is_public)
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
                                    {{-- View --}}
                                    <button type="button"
                                            class="dropdown-item"
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewModal-{{ $announcement->id }}">
                                        <i class="fa-solid fa-eye"></i> View Full Message
                                    </button>

                                    {{-- Public -> Hidden --}}
                                    @if ($announcement->is_public === true)
                                        <button type="button"
                                                class="dropdown-item text-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmHideModal-{{ $announcement->id }}"
                                                data-mode="hide">
                                            <i class="fa-solid fa-ban"></i> Hide
                                        </button>
                                        <form id="hide-announcement-form-{{ $announcement->id }}"
                                                action="{{ route('admin.announcements.hide', $announcement) }}"
                                                method="POST"
                                                class="d-none">
                                            @csrf
                                            @method('PATCH')
                                        </form>
                                    @endif
                                </div>
                            </div>
                            {{-- Modals --}}
                            @include('admin.announcements.modals.view', ['announcement' => $announcement])
                            @include('admin.announcements.modals.hide', ['announcement' => $announcement])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- footer: rows per page (instant) + pagination -->
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0">
                    Showing {{ $announcements->firstItem() }} - {{ $announcements->lastItem() }} of
                    {{ $announcements->total() }}
                </p>
            </div>
            <div class="col-md-4">
                <form id="rowsPerPageForm"
                    method="GET"
                    action="{{ route('admin.announcements.index') }}"
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
                    <input type="hidden" name="keyword" value="{{ request('keyword') }}">
                    <input type="hidden" name="published_date" value="{{ request('published_date') }}">
                    <input type="hidden" name="is_public" value="{{ request('is_public', 'all') }}">
                </form>
            </div>
            <div class="col-md-2 d-flex justify-content-end">
                {{ $announcements->withQueryString()->links() }}
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
