<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'payment_method',
        'status',
        'stripe_session_id',
        'stripe_session_url',
        'payment_intent_id',
        'amount',
        'currency',
        'payment_region',
        'paid_at',
    ];

    protected $casts = [
        'amount'   => 'integer',
        'paid_at'  => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class)->withTrashed();
    }
}
