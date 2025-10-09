<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'message',
        'reservation_id',
        'read_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
