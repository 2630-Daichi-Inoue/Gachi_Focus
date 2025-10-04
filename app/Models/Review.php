<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use SoftDeletes;
    
    # review - user
    # a review belongs to one user
    public function user() {
        return $this->belongsTo(User::class)->withTrashed();
    }

    # review - space
    # a review belongs to one space
    public function space() {
        return $this->belongsTo(Space::class)->withTrashed();
    }
}
