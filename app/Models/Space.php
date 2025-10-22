<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Space extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'name',
        'location_for_overview',
        'location_for_details',
        'min_capacity',
        'max_capacity',
        'area',
        'weekday_price',
        'weekend_price',
        'description',
        'image',
    ];

    # space - categoryspace
    # a space has many categories
    # retrieve all the categories of a space
    public function categorySpace() {
        return $this->hasMany(CategorySpace::class);
    }

    # space - review
    # a space has many reviews
    # get all the reviews of a space
    public function reviews() {
        return $this->hasMany(Review::class);
    }

    # space - reservation
    # a space has many reservations
    # get all the reservations of a space
    public function reservations() {
        return $this->hasMany(Reservation::class);
    }

    # space - photo
    # a space has many photos
    # get all the phosos of a space
    public function photos() {
        return $this->hasMany(Photo::class);
    }

}
