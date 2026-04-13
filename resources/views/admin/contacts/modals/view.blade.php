<div class="modal fade"
    id="viewModal-{{ $contact->id }}"
    tabindex="-1"
    aria-labelledby="viewLabel-{{ $contact->id }}"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-primary">
            <div class="modal-header border-primary">
                <h5 class="modal-title text-wrap"
                    id="viewLabel-{{ $contact->id }}">
                    User: {{ $contact->user->name }}
                    @if ($contact->reservation_id !== null)
                        <br>
                        Space: {{ $contact->reservation->space->name }}
                        <br>
                        Date: {{ $contact->reservation->start_at->format('Y-m-d H:i') }} - {{ $contact->reservation->end_at->format('Y-m-d H:i') }}
                    @endif
                </h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>
            </div>
            <div class="modal-body text-wrap">
                <h5>{{ $contact->title }}</h5>
                <p>{{ $contact->message }}</p>
            </div>
            <div class="modal-footer border-0">
                @if($contact->read_at === null && $contact->contact_status !== 'canceled')
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">Keep it unread.
                    </button>
                    <button type="button"
                            class="btn btn-danger"
                            onclick="document.getElementById('read-contact-form-{{ $contact->id }}').submit();">
                            Mark as read.
                    </button>
                @endif
                @if($contact->contact_status === 'canceled')
                    <p>This contact has been canceled by the user.</p>
                @endif
                @if($contact->read_at !== null && $contact->contact_status === 'open')
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">Keep it open.
                    </button>
                    <button type="button"
                            class="btn btn-danger"
                            onclick="document.getElementById('close-contact-form-{{ $contact->id }}').submit();">
                            Mark as closed.
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
