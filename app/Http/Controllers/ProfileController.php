<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Reservation;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $hasPendingReservations = Reservation::where('user_id', $request->user()->id)
            ->where(function ($q) {
                $q->where('reservation_status', 'pending')
                  ->orWhere(function ($q2) {
                      $q2->where('reservation_status', 'booked')
                         ->where('ended_at', '>', now());
                  });
            })
            ->exists();

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'hasPendingReservations' => $hasPendingReservations,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        $hasPendingReservations = Reservation::where('user_id', $user->id)
            ->where(function ($q) {
                $q->where('reservation_status', 'pending')
                  ->orWhere(function ($q2) {
                      $q2->where('reservation_status', 'booked')
                         ->where('ended_at', '>', now());
                  });
            })
            ->exists();

        if ($hasPendingReservations) {
            return back()->with('error', 'You have pending or upcoming reservations. Please cancel them before deleting your account.');
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
