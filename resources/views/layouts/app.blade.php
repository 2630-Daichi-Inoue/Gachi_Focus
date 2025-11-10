<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'GachiFocus') }} | @yield('title')</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/GachiFocus_logo_favicon.png') }}" type="image/phg">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
        integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <!-- css -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <script src="//unpkg.com/alpinejs" defer></script>
</head>
<style>
    body {
        background-color: #ffffff;
        padding-top: 70px;
    }

    .navbar-custom {
        background-color: #D9D9D9;
    }
</style>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-custom shadow-sm fixed-top">
            <div class="container">
                <a class="navbar-brand"
                    href="{{ auth()->check() && auth()->user()->isAdmin() ? route('admin.home') : route('index') }}">
                    <img src="{{ asset('images/GachiFocus_logo.png') }}" alt="" height="60">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">Login / Register</a>
                                </li>
                            @endif
                        @else
                            @if (auth()->user()->isAdmin())
                                <!-- ← Admin or User -->
                                <!-- ADMIN LINKS -->

                                <a href="{{ route('admin.spaces.register') }}" class="nav-link me-3">Register Coworking Space</a>
                                <a href="{{ route('admin.spaces.index') }}" class="nav-link me-3">Coworking Spaces</a>
                                <a href="{{ route('admin.reservations.index') }}" class="nav-link me-3">Reservations</a>
                                <a href="{{ route('admin.users.index') }}" class="nav-link me-3">Users</a> 

                                {{-- <a href="{{ route('admin.home') }}" class="nav-link me-4 fw-semibold text-dark">
                                    <i class="fa-solid fa-chart-line me-1"></i>Dash board
                                </a> --}}

                                {{-- If admin need to check the user side UI Vr. --}}
                                {{-- <li class="nav-item dropdown">
                                    <a id="userSideDropdown" class="nav-link dropdown-toggle fw-semibold text-dark me-3"
                                        href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        User Side
                                    </a>

                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('reservations.current') }}">
                                                <i class="fa-regular fa-calendar-check me-2 text-secondary"></i>Current
                                                Reservation
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('reservations.past') }}">
                                                <i class="fa-solid fa-clock-rotate-left me-2 text-secondary"></i>Past
                                                Reservation
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('contact.create') }}">
                                                <i class="fa-regular fa-envelope me-2 text-secondary"></i>Contact
                                            </a>
                                        </li>
                                    </ul>
                                </li> --}}

                                <!-- notification -->
                                <li class="nav-item dropdown">
                                    <a id="notificationDropdown" href="" class="nav-link position-relative me-3"
                                        data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-bell"></i>

                                        <!-- Unread mark on the bell icon -->
                                        @php
                                            $hasUnread = auth()
                                                ->user()
                                                ->receivedNotifications()
                                                ->whereNull('read_at')
                                                ->exists();
                                        @endphp
                                        @if ($hasUnread)
                                            <span id="unread-dot" class="position-absolute bg-danger rounded-circle"
                                                style="width: 10px; height: 10px; bottom: 9px; right: 2px;">
                                            </span>
                                        @endif
                                    </a>

                                    <!-- popup window of notifications -->
                                    <div class="dropdown-menu dropdown-menu-end p-3 bg-white"
                                        aria-labelledby="notificationDropdown" style="width: 300px;">
                                        <div style="max-height: 200px; overflow-y: auto; padding: 12px;">
                                            @forelse($notifications as $notification)
                                                <div class="notification-item mb-2">
                                                    <small
                                                        class="text-muted">{{ $notification->created_at->format('M d Y') }}</small>
                                                    <p class="mb-0">{{ $notification->message }}</p>
                                                </div>
                                                <hr class="my-2">
                                            @empty
                                                <p>No notifications found.</p>
                                            @endforelse
                                        </div>

                                        <div class="border-top px-3 py-2 text-end bg-white position-sticky bottom-0">
                                            <a href="{{ route('admin.notifications.index') }}" class="text-primary">All
                                                Notifications &gt;</a>
                                        </div>
                                    </div>
                                </li>
                            @else
                                <!-- USER LINKS -->
                                <!-- Current Reservation -->
                                <a href="{{ route('reservations.current') }}" class="nav-link me-3">Current Reservation</a>

                            <!-- Past Reservation -->
                            <a href="{{ route('reservations.past') }}" class="nav-link me-3">Past Reservation</a>

                                <!-- Contact -->
                                <a href="{{ route('contact.create') }}" class="nav-link me-3">Contact</a>

                                <!-- notification -->
                                <li class="nav-item dropdown">
                                    <a id="notificationDropdown" href="" class="nav-link position-relative me-3"
                                        data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-bell"></i>

                                        <!-- Unread mark on the bell icon -->
                                        @php
                                            $hasUnread = auth()
                                                ->user()
                                                ->receivedNotifications()
                                                ->whereNull('read_at')
                                                ->exists();
                                        @endphp
                                        @if ($hasUnread)
                                            <span id="unread-dot" class="position-absolute bg-danger rounded-circle"
                                                style="width: 10px; height: 10px; bottom: 9px; right: 2px;">
                                            </span>
                                        @endif
                                    </a>

                                    <!-- popup window of notifications -->
                                    <div class="dropdown-menu dropdown-menu-end p-3 bg-white"
                                        aria-labelledby="notificationDropdown" style="width: 300px;">
                                        <div style="max-height: 200px; overflow-y: auto; padding: 12px;">
                                            @forelse($notifications as $notification)
                                                <div class="notification-item mb-2">
                                                    <small
                                                        class="text-muted">{{ $notification->created_at->format('M d Y') }}</small>
                                                    <p class="mb-0">{{ $notification->message }}</p>
                                                </div>
                                                <hr class="my-2">
                                            @empty
                                                <p>No notifications found.</p>
                                            @endforelse

                                        </div>

                                        <div class="border-top px-3 py-2 text-end bg-white position-sticky bottom-0">
                                            <a href="{{ route('notifications.index') }}" class="text-primary">All
                                                Notifications &gt;</a>
                                        </div>
                                    </div>
                                </li>
                            @endif

                            @if (auth()->user()->isAdmin())
                                <i class="fas fa-circle-user text-secondary icon-sm"></i>
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href=""
                                        role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false" v-pre>
                                        {{ Auth::user()->name }}
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                        <a class="dropdown-item" href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                                        document.getElementById('logout-form').submit();">
                                            <i class="fa-solid fa-right-from-bracket"></i> {{ __('Logout') }}
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                            class="d-none">
                                            @csrf
                                        </form>
                                    </div>
                                </li>
                            @else
                                <!-- user icon -->
                                @if (auth()->user()->avatar)
                                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}"
                                        alt="{{ auth()->user()->name }}" class="img-fluid rounded-circle image-sm">
                                @else
                                    <i class="fas fa-circle-user text-secondary icon-sm"></i>
                                @endif
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href=""
                                        role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false" v-pre>
                                        {{ Auth::user()->name }}
                                    </a>

                                    <!-- Profile & logout -->
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                        <a class="dropdown-item" href="{{ route('profile.show', Auth::user()->id) }}"><i
                                                class="fa-solid fa-user"></i> Profile</a>
                                        <a class="dropdown-item" href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                                        document.getElementById('logout-form').submit();">
                                            <i class="fa-solid fa-right-from-bracket"></i> {{ __('Logout') }}
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                            class="d-none">
                                            @csrf
                                        </form>
                                    </div>
                                </li>
<<<<<<< HEAD
                            @endif 
=======
                            @endif
>>>>>>> main
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            <div class="container-xxl">
                
                @if (session('status'))
                <div class="container p-3">
                    <div class="alert alert-success text-center mb-0">
                    {{-- flash message --}}
                    {{ session('status') }}
                    </div>
                </div>

                @endif
                 {{-- page content --}}
                @yield('content')
            </div>
        </main>
    </div>
    @yield('scripts')
</body>

</html>