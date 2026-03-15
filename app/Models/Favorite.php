<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $table = 'favorites';
    // allow mass assignment
    protected $fillable = ['space_id', 'user_id'];
    public $timestamps = false;

    # to get the name of the space
    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    # to get the name of the user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
