<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

class Space extends Model
{
    use SoftDeletes;

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

}
