{{-- common modal --}}
<div class="modal fade" id="reservation-action-{{ $reservation->id }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border border-dark">
      <div class="modal-header">
        <h3 class="h5 modal-title d-flex align-items-center gap-2" id="modal-title-{{ $reservation->id }}">
          {{-- switching title by JS --}}
        </h3>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body" id="modal-body-{{ $reservation->id }}">
        {{-- switching content by JS --}}
      </div>

      <div class="modal-footer border-0">
        <form method="POST" action="{{ route('admin.reservations.action', $reservation->id) }}">
          @csrf
          @method('PATCH')
          <input type="hidden" name="action" id="modal-action-{{ $reservation->id }}" value="">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-sm" id="modal-submit-{{ $reservation->id }}">
            {{-- switching content and color --}}
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
(function initReservationModal{{ $reservation->id }}() {
  const run = () => {
    const modalEl = document.getElementById('reservation-action-{{ $reservation->id }}');
    if (!modalEl) return;

    modalEl.addEventListener('show.bs.modal', (event) => {
      const trigger = event.relatedTarget;                 // 押したボタン
      const mode = trigger?.getAttribute('data-mode');     // "cancel" or "refund"

      const titleEl  = document.getElementById('modal-title-{{ $reservation->id }}');
      const bodyEl   = document.getElementById('modal-body-{{ $reservation->id }}');
      const actionEl = document.getElementById('modal-action-{{ $reservation->id }}');
      const submitEl = document.getElementById('modal-submit-{{ $reservation->id }}');

      if (mode === 'cancel') {
        titleEl.innerHTML = `<i class="fa-solid fa-ban text-danger"></i> Cancel Reservation #{{ $reservation->id }}`;
        bodyEl.innerHTML  = `Are you sure you want to cancel this reservation?<br>
          <small class="text-muted">※ If it's already paid, <span class="fw-bold">refund</span> is necessary.`;
        actionEl.value = 'cancel';
        submitEl.className = 'btn btn-danger btn-sm';
        submitEl.textContent = 'Cancel';
      } else {
        titleEl.innerHTML = `<i class="fa-solid fa-arrow-rotate-left text-primary"></i> Refund Reservation #{{ $reservation->id }}`;
        bodyEl.innerHTML  = `Are you sure you want to make this reservation <span class="fw-bold">Refunded</span>?`;
        actionEl.value = 'refund';
        submitEl.className = 'btn btn-primary btn-sm';
        submitEl.textContent = 'Refund';
      }
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run, { once: true });
  } else {
    run();
  }
})();
</script>