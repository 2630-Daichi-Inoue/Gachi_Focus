@extends('layouts.app')

@section('content')
    <div class="container mt-5 px-0">
        <h1 class="px-3">Admin Home Page</h1>

        <div class="row gx-3">
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">Status Overview</div>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.reservations.index') }}" class="list-group-item">Reservations ></a>
                        <a href="{{ route('admin.users.index') }}" class="list-group-item">Users ></a>
                        <a href="{{ route('admin.spaces.index') }}" class="list-group-item">Coworking Spaces ></a>
                        <a href="{{ route('admin.spaces.create') }}" class="list-group-item">Register Coworking Space ></a>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" id="dashboardTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="chart-tab" data-bs-toggle="tab" href="#chart"
                                    role="tab">Chart</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="summary-tab" data-bs-toggle="tab" href="#summary"
                                    role="tab">Summary</a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body tab-content">
                        <div class="tab-pane fade show active" id="chart" role="tabpanel">
                            <span id="chartMode" class="text-muted">Year</span>
                            <div style="height:400px; position:relative;">
                                <canvas id="salesChart"></canvas>
                                <div id="leftOverlay"
                                    class="overlay d-flex align-items-center justify-content-center start-0">
                                    <span class="arrow">&lt;</span>
                                </div>
                                <div id="rightOverlay"
                                    class="overlay d-flex align-items-center justify-content-center end-0">
                                    <span class="arrow">&gt;</span>
                                </div>
                            </div>
                        </div>

                        {{-- Summary --}}
                        <div class="tab-pane fade" id="summary" role="tabpanel">
                            <div class="row text-center">
                                <div class="col-md-6 mb-3">
                                    <div class="card" style="background-color: rgb(252, 252, 252);">
                                        <div class="card-body">
                                            <h6 class="card-title">Today</h6>
                                            <p class="card-text">${{ $summary['today'] ?? 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card" style="background-color: rgba(223, 249, 251, 1);">
                                        <div class="card-body">
                                            <h6 class="card-title">This Week</h6>
                                            <p class="card-text">${{ $summary['week'] ?? 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card" style="background-color: rgba(232, 246, 243, 1);">
                                        <div class="card-body">
                                            <h6 class="card-title">This Month</h6>
                                            <p class="card-text">${{ $summary['month'] ?? 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card" style="background-color:rgba(252, 243, 207, 1);">
                                        <div class="card-body">
                                            <h6 class="card-title">This Year</h6>
                                            <p class="card-text">${{ $summary['year'] ?? 0 }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-home.css') }}">
@endsection

@section('scripts')
    <!-- Chart.js -->
    <script>
        const monthLabels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        const weekLabels = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];

        const salesDataSets = {
            year: {
                labels: {!! json_encode(array_keys($salesYear)) !!},
                total: {!! json_encode(array_values($salesYear)) !!},
                regions: {!! json_encode($salesByRegionYear) !!}
            },
            month: {
                labels: monthLabels,
                total: {!! json_encode(array_values($salesMonth)) !!},
                regions: {!! json_encode($salesByRegionMonth) !!}
            },
            week: {
                labels: weekLabels,
                total: {!! json_encode(array_values($salesWeek)) !!},
                regions: {!! json_encode($salesByRegionWeek) !!}
            }
        };
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/admin-home.js') }}"></script>
@endsection