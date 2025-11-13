<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes;

    /**
     * Mass-assignable attributes.
     * NOTE: Keep keys aligned with DB columns and existing flows.
     */
    protected $fillable = [
        // Core reservation info
        'user_id',
        'space_id',         // replaces legacy "room"
        'room',             // keep temporarily for backward compatibility
        'type',
        'date',
        'start_time',
        'end_time',
        'adults',
        'facilities',
        'total_price',
        'status',

        // Payment-related
        'payment_status',     // unpaid|paid|canceled|refunded
        'payment_intent_id',  // Stripe pi_xxx
        'amount_paid',        // integer (JPY: yen)
        'paid_at',            // timestamp
        'currency',           // ISO 4217
        'payment_region',     // JP, US, EU, etc.
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'facilities' => 'array',
        'date'       => 'date',
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
        'paid_at'    => 'datetime',
    ];

    /**
     * ---- Constants for filters/labels (controller-safe) ----
     *
     * These two are used by admin controllers like:
     *   array_keys(Reservation::STATUS_MAP)
     *   array_keys(Reservation::PAYMENT_MAP)
     * Do NOT change the keys unless DB values are migrated accordingly.
     */
    public const STATUS_MAP = [
        'pending'   => 'Pending',
        'confirmed' => 'Confirmed',
        'completed' => 'Completed',
        'canceled'  => 'Canceled',
    ];

    public const PAYMENT_MAP = [
        'unpaid'   => 'Unpaid',
        'paid'     => 'Paid',
        'refunded' => 'Refunded',
        'canceled' => 'Canceled',
    ];

    /**
     * ---- UI presentation map (kept separate to avoid breaking logic) ----
     * This preserves your original icon/class mapping by display label.
     * Use when rendering badges:
     *   $label = $reservation->displayStatusLabel();
     *   $badge = Reservation::PAYMENT_UI_MAP[$label] ?? null;
     */
    public const PAYMENT_UI_MAP = [
        'Paid' => [
            'icon'  => 'fa-solid fa-circle-check',
            'class' => 'text-success fw-light',
        ],
        'Unpaid' => [
            'icon'  => 'fa-solid fa-circle-xmark',
            'class' => 'text-danger fw-light',
        ],
        'Refunded' => [
            'icon'  => 'fa-solid fa-arrow-rotate-left',
            'class' => 'text-primary fw-light',
        ],
        'Refund Pending' => [
            'icon'  => 'fa-solid fa-hourglass-start',
            'class' => 'text-warning fw-light',
        ],
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function space()
    {
        return $this->belongsTo(Space::class)->withTrashed();
    }

    public function payment()
    {
        return $this->hasOne(Payment::class)->withTrashed();
    }

    /**
     * Helpers
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function displayAmount(): int
    {
        // amount_paid has priority; fallback to rounded total_price
        return (int) ($this->amount_paid ?? round($this->total_price ?? 0));
    }

    public function displayStatusLabel(): string
    {
        // Normalize DB values to display labels used across UI
        return match ($this->payment_status) {
            'paid'      => 'Paid',
            'refunded'  => 'Refunded',
            'unpaid'    => 'Unpaid',
            'canceled'  => 'Unpaid',   // treat canceled as unpaid in UI
            default     => 'Unpaid',
        };
    }
}
