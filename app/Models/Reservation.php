<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory, HasUlids;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'user_id',
        'space_id',
        'reservation_status',
        'started_at',
        'ended_at',
        'quantity',
        'slot_count',
        'unit_price_yen',
        'total_price_yen',
        'canceled_at',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'quantity'          => 'integer',
        'slot_count'        => 'integer',
        'unit_price_yen'    => 'integer',
        'total_price_yen'   => 'integer',
        'started_at'        => 'datetime',
        'ended_at'          => 'datetime',
        'canceled_at'       => 'datetime',
    ];

    /**
     * Reservation status labels
     */
    public const RESERVATION_STATUS_MAP = [
        'pending'  => 'Pending',
        'booked'   => 'Booked',
        'canceled' => 'Canceled',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function space()
    {
        return $this->belongsTo(Space::class)->withTrashed();
    }

     public function notifications()
    {
        return $this->morphMany(Notification::class, 'related');
    }

     public function payment()
     {
         return $this->hasOne(Payment::class);
     }

     public function review()
     {
         return $this->hasOne(Review::class);
     }

     public function contacts()
     {
         return $this->hasMany(Contact::class);
     }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopePending($query)
    {
        return $query->where('reservation_status', 'pending');
    }

    public function scopeBooked($query)
    {
        return $query->where('reservation_status', 'booked');
    }

    public function scopeCanceled($query)
    {
        return $query->where('reservation_status', 'canceled');
    }

    public function scopeActive($query)
    {
        return $query->where('reservation_status', 'booked')
                    ->where('ended_at', '>', now());
    }

    public function scopePast($query)
    {
        return $query->where('reservation_status', 'booked')
                    ->where('ended_at', '<=', now());
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    public function amount(): int
    {
        return $this->total_price_yen ?? 0;
    }

    public function displayStatusLabel(): string
    {
        return match ($this->reservation_status) {
            'pending'   => 'Pending',
            'booked'    => 'Booked',
            'canceled'  => 'Canceled',
            default     => 'Unknown',
        };
    }

    public function isActive(): bool
    {
        return $this->reservation_status === 'booked'
            && $this->ended_at->isFuture();
    }

    public function isCancelable(): bool
    {
        return $this->reservation_status === 'booked'
            && now()->lt($this->started_at->subHour());
    }

    public function isReviewable(): bool
    {
        return $this->reservation_status !== 'canceled'
            && $this->ended_at->isPast()
            && !$this->review;
    }
}
