<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reservation_id', 'method', 'status', 'created_at', 'updated_at', 'deleted_at'
    ];

    # payment - reservation
    # a payment belongs to one reservation
    public function reservation() {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
