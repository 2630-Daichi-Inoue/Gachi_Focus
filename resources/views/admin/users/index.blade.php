@extends('layouts.app')

@section('title', 'Admin: Users')

@section('content')
    <h2>User list</h2>
    <form method="GET" action="{{ route('admin.users') }}" id="searchForm">
        <div class="row align-items-end g-2 mb-2">

            <!-- Name -->
            <div class="col-md-2">
                <label for="name" class="form-label mb-1 small text-muted">Name</label>
                <div class="position-relative">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-1 text-muted"></i>
                    <input type="search"
                            name="name"
                            id="name"
                            class="form-control form-control-sm border border-dark ps-4"
                            placeholder="Search by name"
                            value="{{ request('name') }}">
                </div>
            </div>

            <!-- Status (instant apply) -->
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
                    <option value="banned" {{ $status==='banned' ? 'selected' : '' }}>Banned</option>
                </select>
            </div>

            <div class="col-md-4 d-flex gap-2 justify-content-end">
                <!-- Clear button -->
                <a href="{{ route('admin.users') }}"
                class="btn btn-sm btn-outline-secondary bg-secondary-subtle text-dark border border-dark w-25">
                    Clear filters
                </a>

                <!-- Submit button-->
                <button type="submit"
                        class="btn btn-sm border border-dark rounded px-3 py-1 text-white fw-bold w-25"
                        style="background-color: #757B9D; letter-spacing: 0.15em;">
                    Search
                </button>
            </div>
        </div>
    </form>

    @if ($all_users->isEmpty())
        {{-- 件数0ならメッセージだけ --}}
        <div class="text-center">
            <h2>No results.</h2>
            <p class="text-secondary">Try different filters or remove them.</p>
        </div>
    @else
        {{-- 件数>0ならテーブルは1つだけ --}}
        <table class="table table-hover align-middle bg-white border text-secondary">
            <thead class="small table-success text-secondary">
                <tr>
                    <th></th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($all_users as $user)
                    <tr>
                        <td>
                            @if ($user->avatar)
                                <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="rounded-circle d-block mx-auto avatar-md">
                            @else
                                <i class="fas fa-circle-user d-block mx-auto text-center icon-md"></i>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('profile.show', $user->id) }}" class="text-decoration-none text-dark">
                                {{ $user->name }}
                            </a>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if ($user->trashed())
                                <i class="fas fa-circle text-danger"></i> <span class="text-danger">Banned</span>
                            @else
                                <i class="fas fa-circle text-success"></i> <span class="text-success">Active</span>
                            @endif
                        </td>
                        <td>
                            @if (Auth::id() !== $user->id)
                                <div class="dropdown">
                                    <button class="btn btn-sm" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        @if ($user->trashed())
                                            <button class="dropdown-item text-primary" data-bs-toggle="modal" data-bs-target="#activate-user-{{ $user->id }}">
                                                <i class="fas fa-user-check"></i> Activate {{ $user->name }}
                                            </button>
                                        @else
                                            <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deactivate-user-{{ $user->id }}">
                                                <i class="fas fa-user-slash"></i> Deactivate {{ $user->name }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                {{-- モーダルは各行の直後でOK --}}
                                @include('admin.users.modals.status')
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- フッター：件数表示 + Rows per page + ページネーション --}}
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0">
                    Showing {{ $all_users->firstItem() }} - {{ $all_users->lastItem() }} of {{ $all_users->total() }}
                </p>
            </div>

            <div class="col-md-4">
                <form id="rowsPerPageForm" method="GET" action="{{ route('admin.users') }}"
                      class="d-flex align-items-center gap-2 justify-content-end">
                    <label for="rows_per_page" class="mb-0 small text-muted">Rows per page:</label>
                    @php $per = (int) request('rows_per_page', 20); @endphp
                    <select name="rows_per_page" id="rows_per_page"
                            class="form-select form-select-sm border-dark text-dark w-auto">
                        <option value="20" {{ $per===20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $per===50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $per===100 ? 'selected' : '' }}>100</option>
                    </select>
                    {{-- 現在のフィルタを維持 --}}
                    <input type="hidden" name="name" value="{{ request('name') }}">
                    <input type="hidden" name="status" value="{{ request('status', 'all') }}">
                </form>
            </div>

            <div class="col-md-2 d-flex justify-content-end">
                {{ $all_users->withQueryString()->links() }}
            </div>
        </div>
    @endif

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