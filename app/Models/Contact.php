<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends Model
{
    use HasFactory, HasUlids;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'user_id',
        'reservation_id',
        'title',
        'message',
        'contact_status',
        'read_at',
        'canceled_at',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'message'        => 'string',
        'title'          => 'string',
        'contact_status' => 'string',
        'read_at'        => 'datetime',
        'canceled_at'    => 'datetime',
    ];

    /**
     * Get the user associated with the contact.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
    * Get the reservation associated with the contact.
    */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
