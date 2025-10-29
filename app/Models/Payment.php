<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reservation_id',
        'method',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * A payment belongs to one reservation.
     * Even if the reservation is soft-deleted, we can still access it.
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class)->withTrashed();
    }
}
