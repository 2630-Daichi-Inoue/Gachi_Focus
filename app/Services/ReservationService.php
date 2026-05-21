<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    public function checkCapacity(Space $space, Carbon $startedAt, Carbon $endedAt, int $quantity): void
    {
        $overlappingQuantity = Reservation::query()
            ->where('space_id', $space->id)
            ->whereIn('reservation_status', ['booked', 'pending'])
            ->where('started_at', '<', $endedAt)
            ->where('ended_at', '>', $startedAt)
            ->sum('quantity');

        if ($overlappingQuantity + $quantity > $space->capacity) {
            throw ValidationException::withMessages([
                'quantity' => 'Sorry, but there are not enough spaces available for the selected time slot.',
            ]);
        }
    }

    public function buildStartCandidates(Space $space, string $date): array
    {
        $openTime = Carbon::createFromFormat('Y-m-d H:i:s', "$date {$space->open_time}");
        $closeTime = Carbon::createFromFormat('Y-m-d H:i:s', "$date {$space->close_time}");

        $cursorOpenTime = $openTime->copy();
        $lastStartedAt = $closeTime->copy()->subMinutes(30);

        if (Carbon::parse($date)->isToday()) {
            $now = Carbon::now();
            $minute = $now->minute;

            if ($minute === 0 || $minute === 30) {
                $roundedNow = $now->copy()->second(0)->microsecond(0);
            } elseif ($minute < 30) {
                $roundedNow = $now->copy()->minute(30)->second(0)->microsecond(0);
            } else {
                $roundedNow = $now->copy()->addHour()->minute(0)->second(0)->microsecond(0);
            }

            if ($roundedNow->gt($openTime)) {
                $cursorOpenTime = $roundedNow->copy();
            }
        }

        $startCandidates = [];
        while ($cursorOpenTime->lte($lastStartedAt)) {
            $startCandidates[] = $cursorOpenTime->format('H:i');
            $cursorOpenTime->addMinutes(30);
        }

        return [
            'startCandidates' => $startCandidates,
            'lastStartedAt' => $lastStartedAt->format('H:i'),
        ];
    }

    public function calculatePaymentPreview(Space $space, array $data): array
    {
        $newStartedAt = Carbon::parse($data['date'].' '.$data['started_at']);
        $newEndedAt = Carbon::parse($data['date'].' '.$data['ended_at']);

        $this->checkCapacity($space, $newStartedAt, $newEndedAt, $data['quantity']);

        $conflictingReservations = Reservation::query()
            ->where('user_id', Auth::id())
            ->whereIn('reservation_status', ['booked', 'pending'])
            ->where('ended_at', '>', now())
            ->where('started_at', '<', $newEndedAt)
            ->where('ended_at', '>', $newStartedAt)
            ->with('space:id,name')
            ->get(['id', 'space_id', 'started_at', 'ended_at']);

        $unitPriceYen = $space->getUnitPriceForDate(Carbon::parse($data['date']));
        $slotCount = $newStartedAt->diffInMinutes($newEndedAt) / 30;

        return [
            'reservationData' => [
                'date' => $data['date'],
                'started_at' => $data['started_at'],
                'ended_at' => $data['ended_at'],
                'quantity' => $data['quantity'],
                'total_price_yen' => $unitPriceYen * $data['quantity'] * $slotCount,
            ],
            'conflictingReservations' => $conflictingReservations,
        ];
    }

    public function createPendingReservation(Space $space, array $data): Reservation
    {
        $newStartedAt = Carbon::parse($data['date'].' '.$data['started_at']);
        $newEndedAt = Carbon::parse($data['date'].' '.$data['ended_at']);

        return DB::transaction(function () use ($space, $data, $newStartedAt, $newEndedAt) {
            $lockedSpace = Space::whereKey($space->id)->lockForUpdate()->firstOrFail();

            $this->checkCapacity($lockedSpace, $newStartedAt, $newEndedAt, $data['quantity']);

            $unitPriceYen = $lockedSpace->getUnitPriceForDate(Carbon::parse($data['date']));
            $slotCount = $newStartedAt->diffInMinutes($newEndedAt) / 30;

            return Reservation::create([
                'user_id' => Auth::id(),
                'space_id' => $lockedSpace->id,
                'reservation_status' => 'pending',
                'started_at' => $newStartedAt,
                'ended_at' => $newEndedAt,
                'quantity' => $data['quantity'],
                'slot_count' => $slotCount,
                'unit_price_yen' => $unitPriceYen,
                'total_price_yen' => $unitPriceYen * $data['quantity'] * $slotCount,
            ]);
        });
    }

    public function cancelPendingReservation(Reservation $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            $reservation->update([
                'reservation_status' => 'canceled',
                'canceled_at' => now(),
            ]);

            $reservation->payments()
                ->where('status', 'pending')
                ->update(['status' => 'canceled']);
        });
    }
}
