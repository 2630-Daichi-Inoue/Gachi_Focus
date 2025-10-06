<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    protected $fillable = ['space_id', 'path'];

    public function space()
    {
        return $this->belongsTo(Space::class);
    }
}
