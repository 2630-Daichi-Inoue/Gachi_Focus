<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes;

    /**
     * Mass-assignable attributes.
     * NOTE: Use space_id (FK) instead of legacy "room" string.
     */
    protected $fillable = [
        // Core reservation info
        'user_id',
        'space_id',        // FK to spaces.id (replaces legacy "room")
        'type',
        'date',
        'start_time',
        'end_time',
        'adults',
        'facilities',
        'total_price',

        // Payment-related
        'payment_status',      // unpaid|paid|canceled|refunded
        'payment_intent_id',   // Stripe pi_xxx (or provider-specific)
        'amount_paid',         // integer in smallest unit (JPY: yen)
        'paid_at',             // timestamp
        'currency',            // ISO 4217 (e.g., JPY, USD)
        'payment_region',      // market/region (e.g., JP, US, EU, AU)
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'facilities' => 'array',
        'date'       => 'date',
        'paid_at'    => 'datetime',
    ];

    /**
     * UI mapping for payment badges/icons (kept as-is).
     * Keys are display labels; not necessarily equal to DB values.
     */
    public const PAYMENT_MAP = [
        'Paid' => [
            'icon'  => 'fa-solid fa-circle-check ',
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
    // A reservation belongs to one user (allow showing soft-deleted users)
    public function user() {
        return $this->belongsTo(User::class)->withTrashed();
    }

    // A reservation belongs to one space (allow showing soft-deleted spaces)
    public function space() {
        return $this->belongsTo(Space::class)->withTrashed();
    }

    // A reservation has one payment (allow showing soft-deleted payments)
    public function payment() {
        return $this->hasOne(Payment::class)->withTrashed();
    }

    /**
     * Helpers
     */
    // True if the payment is fully settled
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    // Display amount with sensible fallback (amount_paid > total_price > 0)
    public function displayAmount(): int
    {
        return (int) ($this->amount_paid ?? round($this->total_price ?? 0));
    }

    // Optional: normalize DB status (e.g., to map into PAYMENT_MAP labels)
    public function displayStatusLabel(): string
    {
        // Map DB statuses to display labels used in PAYMENT_MAP
        return match ($this->payment_status) {
            'paid'      => 'Paid',
            'refunded'  => 'Refunded',
            'unpaid'    => 'Unpaid',
            'canceled'  => 'Unpaid',          // or another label if you prefer
            default     => 'Unpaid',
        };
    }
}
