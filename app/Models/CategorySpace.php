<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategorySpace extends Model
{
    // category_spaces - category_space
    protected $table = 'category_space';
    // allow mass assignment
    protected $fillable = ['space_id', 'category_id'];
    public $timestamps = false;

    # to get the name of the category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    # to get the name of the space
    public function space()
    {
        return $this->belongsTo(Space::class);
    }
}