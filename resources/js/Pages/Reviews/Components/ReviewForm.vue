<script setup>
import { Link, router } from '@inertiajs/vue3'
import { reactive, computed, ref } from 'vue'
import Vue3StarRating from 'vue3-star-ratings'
import DeleteReviewModal from './DeleteReviewModal.vue'

const props = defineProps({
    reservation: Object,
    review: Object,
})

const form = reactive({
    rating: Math.round(Number(props.review?.rating ?? 1)),
    comment: props.review?.comment ?? '',
})

const submitReview = () => {
    if (props.review) {
        router.patch(route('reviews.update', props.reservation.id), {
            rating: form.rating,
            comment: form.comment,
        })
    } else {
        router.post(route('reviews.store', props.reservation.id), {
            rating: form.rating,
            comment: form.comment,
        })
    }
}

const ratingProxy = computed({
    get() {
        return form.rating
    },
    set(value) {
        form.rating = Math.round(Number(value))
    }
})

const showDeleteModal = ref(false);

const deleteReview = () => {
    // Call the API to delete the review
    router.delete(route('reviews.destroy', props.reservation.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
        },
        onError: () => {
            alert('Failed to delete the review. Please try again.');
        },
    })
};


</script>

<template>
<div class="bg-white border border-gray-300 p-4">
    <form @submit.prevent="submitReview" class="space-y-4">

        <div class="border border-gray-300 p-4 space-y-4">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="w-full md:w-1/2">
                    <img :src="reservation.space.image_path ? `/storage/${reservation.space.image_path}` : '/images/no-image.png'"
                        :alt="`space ${reservation.space.name}`"
                        class="object-cover rounded border border-gray-300"
                    >
                </div>

                <div class="w-full md:w-1/2">
                    <h1 class="text-3xl font-bold mb-2">{{ reservation.space.name }}</h1>
                </div>
            </div>

            <div class="w-full">
                <h2 class="text-2xl text-gray-500">Rating</h2>
                <Vue3StarRating
                            v-model="ratingProxy"
                            :increment="1"
                            :numberOfStars="5"
                            :starSize="30"
                            starColor="#fbbf24"
                            inactiveColor="#e0e0e0"
                            :show-rating="false"
                >
                </Vue3StarRating>
            </div>

            <div class="w-full">
                <label for="comment" class="text-2xl text-gray-500">Comment <span class="text-lg">(Optional)</span></label>
                <textarea id="comment"
                        v-model="form.comment"
                        placeholder="Write your review here."
                        class="w-full border border-gray-300 rounded p-2"
                        rows="5">
                </textarea>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-4">
            <Link :href="route('reservations.index')"
                    :class="review ? 'md:w-1/3' : 'md:w-1/2'"
                    class="flex items-center justify-center text-black text-3xl border border-gray-500 rounded transition hover:bg-gray-200 p-2">
                Go Back
            </Link>
            <template v-if="!review?.deleted_at">
                <button v-if="review"
                        type="button"
                        @click="showDeleteModal = true"
                        class="flex items-center justify-center md:w-1/3 text-red-500 font-bold text-3xl border border-red-500 rounded transition hover:bg-red-200">
                    Delete Review
                </button>
                <button type="submit"
                        :class="review ? 'md:w-1/3' : 'md:w-1/2'"
                        class="flex items-center justify-center text-white font-bold text-3xl border border-gray-500 rounded transition bg-cyan-600 hover:bg-cyan-700">
                    {{ review ? 'Update Review' : 'Submit Review' }}
                </button>
            </template>
        </div>
    </form>
    <Transition name="modal-fade">
        <DeleteReviewModal
            v-if="showDeleteModal"
            :reservation="reservation"
            @close="showDeleteModal = false"
            @confirm="deleteReview"
        />
    </Transition>
</div>
</template>
