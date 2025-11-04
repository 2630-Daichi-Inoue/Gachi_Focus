<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'GachiFocus') }} | @yield('title')</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/GachiFocus_logo_favicon.png') }}" type="image/png">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <!-- Alpine.js -->
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
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{ asset('images/GachiFocus_logo.png') }}" alt="" height="60">
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto"></ul>

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
                            <!-- Admin or User -->
                            @if (auth()->user()->isAdmin())
                                <!-- ADMIN LINKS -->
                                <a href="{{ route('admin.space.register') }}" class="nav-link me-3">Register Coworking
                                    Space</a>
                                <a href="" class="nav-link me-3">Coworking Spaces</a>
                                <a href="{{ route('admin.reservations') }}" class="nav-link me-3">Reservations</a>
                                <a href="{{ route('admin.users') }}" class="nav-link me-3">Users</a>

                                <!-- Notification -->
                                <li class="nav-item dropdown">
                                    <a id="notificationDropdown" href="#" class="nav-link position-relative me-3"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-solid fa-bell"></i>

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
                                                style="width: 10px; height: 10px; bottom: 9px; right: 2px;"></span>
                                        @endif
                                    </a>

                                    <!-- Popup window of notifications -->
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

                                <!-- Notification -->
                                <li class="nav-item dropdown">
                                    <a id="notificationDropdown" href="#" class="nav-link position-relative me-3"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-solid fa-bell"></i>

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
                                                style="width: 10px; height: 10px; bottom: 9px; right: 2px;"></span>
                                        @endif
                                    </a>

                                    <!-- Popup window of notifications -->
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

                            <!-- Common Profile / Logout Section -->
                            <li class="nav-item dropdown">
                                @if (auth()->user()->avatar)
                                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}"
                                        alt="{{ auth()->user()->name }}" class="img-fluid rounded-circle image-sm me-2"
                                        style="width: 34px; height: 34px; object-fit: cover;">
                                @else
                                    <i class="fas fa-circle-user text-secondary icon-sm me-1"></i>
                                @endif

                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <!-- Profile & Logout -->
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('profile.show', Auth::user()->id) }}">
                                        <i class="fa-solid fa-user"></i> Profile
                                    </a>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                        class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            <div class="container-xxl">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
