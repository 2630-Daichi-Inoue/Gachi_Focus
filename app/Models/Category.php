<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name'];

    # category - category_space (a category has many spaces through category_space)
    public function categorySpace()
    {
        return $this->hasMany(CategorySpace::class);
    }
}
