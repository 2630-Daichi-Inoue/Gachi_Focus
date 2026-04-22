<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AmenitySpace extends Model
{
    protected $table = 'amenity_space';
    // allow mass assignment
    protected $fillable = ['space_id', 'amenity_id'];
    public $timestamps = false;

    # to get the name of the amenity
    public function amenity()
    {
        return $this->belongsTo(Amenity::class);
    }

    # to get the name of the space
    public function space()
    {
        return $this->belongsTo(Space::class);
    }
}
