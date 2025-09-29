@props([
  'title' => '',
  'show' => false,
  'close' => '$dispatch("close")', 
])

<div
  x-data="{ open: @js($show) }"
  x-on:open.window="open = true"
  x-on:close.window="open = false"
  x-show="open"
  x-trap.noscroll="open"
  x-transition.opacity
  class="fixed inset-0 z-50"
  aria-labelledby="modal-title"
  role="dialog"
  aria-modal="true"
>
  <!-- backdrop -->
  <div class="fixed inset-0 bg-black/40" x-on:click="open=false" x-show="open" x-transition.opacity></div>

  <!-- panel -->
  <div
    class="relative mx-auto mt-24 w-[92%] max-w-lg rounded-xl bg-white shadow-xl"
    x-show="open"
    x-transition.scale.origin.center
    x-on:keydown.escape.window="open=false"
  >
    <!-- header -->
    <div class="flex items-center justify-between px-6 py-4">
      <h3 id="modal-title" class="text-lg font-semibold">{{ $title }}</h3>
      <button type="button" class="rounded p-1 text-gray-400 hover:text-gray-600" x-on:click="open=false" aria-label="Close">
        &times;
      </button>
    </div>

    <!-- body -->
    <div class="px-6 pb-6">
      {{ $slot }}
    </div>
  </div>
</div>
