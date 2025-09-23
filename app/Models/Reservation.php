<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
     protected $fillable = [
        'room_slug','type','date','time_from','time_to','adults','facilities','note','user_id'
    ];
    protected $casts = [
        'date' => 'date',
        'facilities' => 'array',
    ];
}
