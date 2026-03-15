<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Amenity extends Model
{

    use HasFactory, HasUlids;

    protected $fillable = ['name'];

    public function spaces()
    {
        return $this->belongsToMany(
            Space::class,
            'amenity_space',
            'amenity_id',
            'space_id'
        );
    }
}
