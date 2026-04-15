<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Announcement extends Model
{
    use HasUlids, HasFactory;

    protected $fillable = [
        'title',
        'message',
        'is_public',
        'published_at',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];
}
