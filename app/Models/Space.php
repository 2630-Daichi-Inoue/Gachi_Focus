<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Space extends Model
{
    protected $fillable = [
        'name', 'address', 'description', 'capacity_min', 'capacity_max',
        'weekday_price', 'weekend_price', 'map_embed', 'rating', 'facilities'
    ];

    protected $casts = [
        'facilities' => 'array',
    ];

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }
}
