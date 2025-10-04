<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title', 'GachiFocus')</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-800">

  <!-- Navbar -->
  <nav class="sticky top-0 z-50 bg-gray-100/80 backdrop-blur supports-[backdrop-filter]:bg-gray-100/60 border-b">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
      
      <!-- Logo -->
      <a href="/" class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-full bg-gray-800 text-white grid place-content-center font-bold">GF</div>
        <span class="font-semibold text-xl">Gachi Focus</span>
      </a>
      
      <!-- Links -->
      <div class="flex items-center gap-8">
        <a href="#" class="hover:text-gray-600">Current Reservation</a>
        <a href="#" class="hover:text-gray-600">Past Reservation</a>
        <a href="#" class="hover:text-gray-600">Contact</a>
        
        <!-- Profile -->
        <div class="flex items-center gap-2">
          <div class="w-9 h-9 rounded-full bg-gray-300 grid place-content-center">👤</div>
          <span>John Doe</span>
        </div>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <main class="max-w-7xl mx-auto px-4 py-8">
    @if(session('success'))
      <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 text-green-800">
        {{ session('success') }}
      </div>
    @endif

    @yield('content')
  </main>
</body>
</html>
