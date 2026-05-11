<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'title',
        'message',
        'is_public',
        'published_at',
        'expired_at',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];
}
