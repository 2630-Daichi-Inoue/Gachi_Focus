@extends('layouts.app')

@section('content')
    <div class="container mt-5 px-0">
        <h2 class="px-3 fw-bold mb-4">Dashboard</h2>

        <div class="row gx-3">
            <div class="col-md-4">
                <!-- Menu -->
                <div class="card mb-3 shadow-sm">
                    <div class="card-header fw-bold" style="font-weight: 800; letter-spacing: 0.8px; font-size: 1.1rem;">
                        Menu
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.reservations.index') }}" class="list-group-item fw-bold">Manage Reservations
                            ></a>
                        <a href="{{ route('admin.users.index') }}" class="list-group-item fw-bold">Manage Users ></a>
                        <a href="{{ route('admin.spaces.index') }}" class="list-group-item fw-bold">Edit Coworking Spaces
                            ></a>
                        <a href="{{ route('admin.spaces.register') }}" class="list-group-item fw-bold">Register
                            Coworking
                            Space ></a>
                        <a href="{{ route('categories.index') }}" class="list-group-item fw-bold">Edit Categories
                            ></a>
                    </div>
                </div>

                <!--  Summary -->
                <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:15px; margin-top:20px;">
                    <div
                        style="border-radius:20px;padding:15px;backdrop-filter:blur(8px);
                    border:1px solid rgba(255,255,255,0.4);box-shadow:0 4px 12px rgba(0,0,0,0.05);
                    background:rgba(252,252,252,0.8);text-align:center;">
                        <h6 style="margin-bottom:4px;font-weight:600;color:#666;">Today</h6>
                        <p style="font-size:1.25rem;font-weight:700;margin-bottom:0;">${{ $summary['today'] ?? 0 }}</p>
                    </div>
                    <div
                        style="border-radius:20px;padding:15px;backdrop-filter:blur(8px);
                    border:1px solid rgba(255,255,255,0.4);box-shadow:0 4px 12px rgba(0,0,0,0.05);
                    background:rgba(223,249,251,0.8);text-align:center;">
                        <h6 style="margin-bottom:4px;font-weight:600;color:#666;">This Week</h6>
                        <p style="font-size:1.25rem;font-weight:700;margin-bottom:0;">${{ $summary['week'] ?? 0 }}</p>
                    </div>
                    <div
                        style="border-radius:20px;padding:15px;backdrop-filter:blur(8px);
                    border:1px solid rgba(255,255,255,0.4);box-shadow:0 4px 12px rgba(0,0,0,0.05);
                    background:rgba(232,246,243,0.8);text-align:center;">
                        <h6 style="margin-bottom:4px;font-weight:600;color:#666;">This Month</h6>
                        <p style="font-size:1.25rem;font-weight:700;margin-bottom:0;">${{ $summary['month'] ?? 0 }}</p>
                    </div>
                    <div
                        style="border-radius:20px;padding:15px;backdrop-filter:blur(8px);
                    border:1px solid rgba(255,255,255,0.4);box-shadow:0 4px 12px rgba(0,0,0,0.05);
                    background:rgba(252,243,207,0.8);text-align:center;">
                        <h6 style="margin-bottom:4px;font-weight:600;color:#666;">This Year</h6>
                        <p style="font-size:1.25rem;font-weight:700;margin-bottom:0;">${{ $summary['year'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- chart -->
            <div class="col-md-8">
                <div class="card shadow-sm" style="background:linear-gradient(180deg,#fdfdfd,#f3f4fa);">
                    <div class="card-header fw-bold" style="font-weight: 800; letter-spacing: 0.8px; font-size: 1.1rem;">
                        Sales Chart</div>
                    <div class="card-body">
                        <span id="chartMode" class="text-muted">Year</span>
                        <div style="height:400px;position:relative;">
                            <canvas id="salesChart"></canvas>

                            <!-- click bar -->
                            <div id="leftBar"
                                style="
                            position:absolute;top:0;left:0;width:3px;height:100%;
                            background:rgba(255,255,255,0);cursor:pointer;
                            transition:background 0.2s ease;">
                            </div>

                            <div id="rightBar"
                                style="
                            position:absolute;top:0;right:0;width:3px;height:100%;
                            background:rgba(255,255,255,0);cursor:pointer;
                            transition:background 0.2s ease;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const salesDataSets = {
            year: {
                labels: {!! json_encode(array_keys($salesYear)) !!},
                total: {!! json_encode(array_values($salesYear)) !!},
                regions: {!! json_encode($salesByRegionYear) !!},
                countries: {!! json_encode($countriesYear) !!}
            },
            month: {
                labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                total: {!! json_encode(array_values($salesMonth)) !!},
                regions: {!! json_encode($salesByRegionMonth) !!},
                countries: {!! json_encode($countriesMonth) !!}
            },
            week: {
                labels: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
                total: {!! json_encode(array_values($salesWeek)) !!},
                regions: {!! json_encode($salesByRegionWeek) !!},
                countries: {!! json_encode($countriesWeek) !!}
            }
        };

        console.log('salesDataSets:', salesDataSets);
    </script>

    <script src="{{ asset('js/admin-home.js') }}"></script>
@endsection
