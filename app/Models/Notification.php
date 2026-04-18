<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Notification extends Model
{
    use HasFactory, HasUlids;

    protected $casts = [
        'read_at' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'related_type',
        'related_id',
        'read_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
