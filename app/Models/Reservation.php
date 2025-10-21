<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes;

     //  allow mass assignment
        protected $fillable = [
            'user_id',
            'room',
            'type',
            'date',
            'start_time',
            'end_time',
            'adults',
            'facilities',
            'total_price',

            // payment related
        'payment_status',      // unpaid|paid|canceled|refunded
        'payment_intent_id',   // Stripe pi_xxx
        'amount_paid',         // integer in smallest unit (JPY: yen)
        'paid_at',             // timestamp
        'currency',            // ISO 4217 (e.g., JPY, USD)
        'payment_region',      // region/market (e.g., JP, US, EU, AU)
        ];

    // cast JSON/date fields
    protected $casts = [
        'facilities' => 'array',
        'date'       => 'date',
    ];


    public const PAYMENT_MAP = [
        'Paid' => [
            'icon'  => 'fa-solid fa-circle-check ',
            'class' => 'text-success fw-light'
        ],
        'Unpaid' => [
            'icon'  => 'fa-solid fa-circle-xmark',
            'class' => 'text-danger fw-light'
        ],
        'Refunded' => [
            'icon'  => 'fa-solid fa-arrow-rotate-left',
            'class' => 'text-primary fw-light'
        ],
        'Refund Pending' => [
            'icon'  => 'fa-solid fa-hourglass-start',
            'class' => 'text-warning fw-light'
        ]
    ];

    # reservation - user
    # a reservation belongs to one user
    public function user() {
        return $this->belongsTo(User::class)->withTrashed();
    }


    # reservation - space
    # a reservation belongs to one space
    public function space() {
        return $this->belongsTo(Space::class)->withTrashed();
    }


    # reservation - payment
    # a reservation has one payment
    public function payment() {
        return $this->hasOne(Payment::class)->withTrashed();
    }

}
    // cast types
    protected $casts = [
        'facilities' => 'array',  // keep JSON as php array
        'paid_at'    => 'datetime',
    ];

    // simple helper
    public function isPaid(): bool
    {
        return ($this->payment_status === 'paid');
    }

    // simple helper: display amount (fallback to total_price)
    public function displayAmount(): int
    {
        // amount_paid is real charge; otherwise fall back to quote
        return (int) ($this->amount_paid ?? round($this->total_price ?? 0));
    }
}
