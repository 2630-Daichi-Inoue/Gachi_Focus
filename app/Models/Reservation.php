<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
     protected $fillable = [
        'room_slug','type','date','start_time','end_time','adults','facilities','note','user_id'
    ];
    protected $casts = [
        'date' => 'date',
        'facilities' => 'array',
    ];
}
