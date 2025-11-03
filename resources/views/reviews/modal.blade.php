<div class="modal fade" id="writeReviewModal" tabindex="-1" aria-labelledby="writeReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 720px;">
        <div class="modal-content p-4 border-0 shadow-sm">

            {{-- Header --}}
            <div class="modal-header border-0 pb-0">
                <h3 class="modal-title fw-bold" id="writeReviewModalLabel">Write a Review</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Form --}}
            <form action="{{ route('reviews.store', ['space' => $space->id]) }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                <div class="modal-body">
                    {{-- Rating Section --}}
                    <div class="mb-3 border-bottom pb-3">
                        <label class="form-label fw-semibold d-block mb-2">Ratings</label>

                        {{-- Cleanliness --}}
                        <div class="d-flex align-items-center mb-3">
                            <span class="me-3" style="width:180px;">Cleanliness</span>
                            @for ($i = 1; $i <= 5; $i++)
                                <input type="radio" name="cleanliness" value="{{ $i }}"
                                    id="clean{{ $i }}" class="d-none">
                                <label for="clean{{ $i }}" style="cursor:pointer;">
                                    <i class="fa-regular fa-star fa-lg text-warning"
                                        id="icon-clean{{ $i }}"></i>
                                </label>
                            @endfor
                        </div>

                        {{-- Property Conditions --}}
                        <div class="d-flex align-items-center mb-3">
                            <span class="me-3" style="width:180px;">Property Conditions</span>
                            @for ($i = 1; $i <= 5; $i++)
                                <input type="radio" name="conditions" value="{{ $i }}"
                                    id="cond{{ $i }}" class="d-none">
                                <label for="cond{{ $i }}" style="cursor:pointer;">
                                    <i class="fa-regular fa-star fa-lg text-warning"
                                        id="icon-cond{{ $i }}"></i>
                                </label>
                            @endfor
                        </div>

                        {{-- Facilities --}}
                        <div class="d-flex align-items-center">
                            <span class="me-3" style="width:180px;">Facilities</span>
                            @for ($i = 1; $i <= 5; $i++)
                                <input type="radio" name="facilities" value="{{ $i }}"
                                    id="fac{{ $i }}" class="d-none">
                                <label for="fac{{ $i }}" style="cursor:pointer;">
                                    <i class="fa-regular fa-star fa-lg text-warning"
                                        id="icon-fac{{ $i }}"></i>
                                </label>
                            @endfor
                        </div>
                    </div>

                    {{-- Comment --}}
                    <div class="mb-3">
                        <label for="comment" class="form-label fw-semibold">Comment</label>
                        <textarea name="comment" id="comment" class="form-control" rows="3" placeholder="Write your review here..."></textarea>
                    </div>

                    {{-- Photo upload --}}
                    <div class="mb-3">
                        <label for="photo" class="form-label fw-semibold">Upload Photo (optional)</label>
                        <div class="input-group">
                            <input type="file" name="photo" id="photo" class="form-control" accept="image/*">
                            <button type="button" class="btn btn-outline-secondary"
                                onclick="document.getElementById('photo').click()">
                                <i class="fa-solid fa-upload me-1"></i> Choose File
                            </button>
                        </div>

                        <div id="currentPhotoContainer" class="d-none mt-3">
                            <label class="form-label fw-semibold d-block">Current Photo</label>
                            <div class="position-relative d-inline-block"
                                style="background-color: rgba(240,240,240,0.6); border-radius: 8px; padding: 6px;">
                                <img id="currentPhotoImg" src="" alt="Current review photo"
                                    style="width: 120px; border-radius: 8px; opacity: 0.9;">

                                <button type="button" id="removePhotoButton"
                                    class="btn btn-sm btn-danger position-absolute top-0 end-0 translate-middle"
                                    style="border-radius: 50%; width: 22px; height: 22px; line-height: 18px; padding: 0;">
                                    <i class="fa-solid fa-xmark" style="font-size: 0.8rem;"></i>
                                </button>
                            </div>

                            <input type="hidden" name="remove_photo" id="remove_photo" value="0">
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="modal-footer border-0 d-flex justify-content-end gap-2">
                    {{-- Cancel --}}
                    <button type="button" class="btn px-4" data-bs-dismiss="modal"
                        style="color: rgba(166, 75, 75, 1); border: 1.5px solid rgba(166, 75, 75, 1); background: transparent;">
                        Cancel
                    </button>

                    {{-- Submit --}}
                    <button type="submit" class="btn text-white px-4"
                        style="background-color: rgba(84, 127, 161, 1); border:none;">
                        Submit Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- ======= --}}
{{-- JS part --}}
{{-- ======= --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reviewModal = document.getElementById('writeReviewModal');
        if (!reviewModal) return;

        ['clean', 'cond', 'fac'].forEach(prefix => {
            for (let i = 1; i <= 5; i++) {
                const icon = document.getElementById(`icon-${prefix}${i}`);
                const input = document.getElementById(`${prefix}${i}`);
                if (icon && input) {
                    icon.addEventListener('click', () => {
                        for (let j = 1; j <= 5; j++) {
                            const targetIcon = document.getElementById(`icon-${prefix}${j}`);
                            const targetInput = document.getElementById(`${prefix}${j}`);
                            if (targetIcon && targetInput) {
                                if (j <= i) {
                                    targetIcon.classList.add('fa-solid');
                                    targetIcon.classList.remove('fa-regular');
                                } else {
                                    targetIcon.classList.remove('fa-solid');
                                    targetIcon.classList.add('fa-regular');
                                }
                            }
                        }
                        input.checked = true;
                    });
                }
            }
        });

        reviewModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const form = reviewModal.querySelector('form');
            const commentField = form.querySelector('#comment');
            const currentPhotoContainer = form.querySelector('#currentPhotoContainer');
            const currentPhotoImg = form.querySelector('#currentPhotoImg');
            const removePhotoInput = form.querySelector('#remove_photo');
            const removePhotoButton = form.querySelector('#removePhotoButton');

            // edit
            if (button && button.hasAttribute('data-review-id')) {
                const id = button.dataset.reviewId;
                const cleanliness = button.dataset.cleanliness;
                const conditions = button.dataset.conditions;
                const facilities = button.dataset.facilities;
                const comment = button.dataset.comment;
                const photo = button.dataset.photo;

                form.action = `/reviews/${id}`;
                let methodInput = form.querySelector('input[name="_method"]');
                if (!methodInput) {
                    methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    form.appendChild(methodInput);
                }
                methodInput.value = 'PUT';
                commentField.value = comment || '';

                const map = {
                    clean: cleanliness,
                    cond: conditions,
                    fac: facilities
                };
                Object.entries(map).forEach(([prefix, value]) => {
                    for (let i = 1; i <= 5; i++) {
                        const icon = document.getElementById(`icon-${prefix}${i}`);
                        const input = document.getElementById(`${prefix}${i}`);
                        if (!icon || !input) continue;
                        input.checked = (i == value);
                        icon.classList.toggle('fa-solid', i <= value);
                        icon.classList.toggle('fa-regular', i > value);
                    }
                });

                if (photo && currentPhotoContainer && currentPhotoImg) {
                    currentPhotoContainer.classList.remove('d-none');
                    currentPhotoImg.src = `/storage/${photo}`;
                    if (removePhotoInput) removePhotoInput.value = '0';
                } else if (currentPhotoContainer) {
                    currentPhotoContainer.classList.add('d-none');
                }

                if (removePhotoButton && removePhotoInput) {
                    removePhotoButton.onclick = function() {
                        currentPhotoContainer.classList.add('d-none');
                        currentPhotoImg.src = '';
                        removePhotoInput.value = '1';
                    };
                }
            }

            // new
            else {
                form.querySelector('#comment').value = '';
                form.querySelectorAll('input[type=radio]').forEach(i => i.checked = false);

                form.action = "{{ url('reviews/' . $space->id) }}";
                form.method = 'POST';

                const methodInput = form.querySelector('input[name="_method"]');
                if (methodInput) methodInput.remove();

                // reset stars
                ['clean', 'cond', 'fac'].forEach(prefix => {
                    for (let i = 1; i <= 5; i++) {
                        const icon = document.getElementById(`icon-${prefix}${i}`);
                        if (icon) {
                            icon.classList.remove('fa-solid');
                            icon.classList.add('fa-regular');
                        }
                    }
                });

                if (currentPhotoContainer) currentPhotoContainer.classList.add('d-none');
                if (removePhotoInput) removePhotoInput.value = '0';
            }
        });
    });
</script>
