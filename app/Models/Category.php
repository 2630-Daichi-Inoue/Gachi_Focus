<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    # CATEGORY - CATEGORYPOST
    # count the number of post in a category
    public function categoryPost()
    {
        return $this->hasMany(CategoryPost::class);   
    }
}
