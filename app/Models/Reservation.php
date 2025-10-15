<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;


class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'space_id',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];


    public const STATUS_MAP = [
        'Completed' => [
            'icon'  => 'fa-solid fa-circle-check ',
            'class' => 'badge bg-primary text-white rounded-pill fw-light fs-6'
        ],
        'Active' => [
            'icon'  => 'fa-solid fa-circle-check',
            'class' => 'badge bg-success text-white rounded-pill fw-light fs-6'
        ],
        'Cancelled' => [
            'icon'  => '',
            'class' => 'badge bg-secondary text-white rounded-pill fw-light fs-6'
        ]
    ];


    public const PAYMENT_MAP = [
        'Paid' => [
            'icon'  => 'fa-solid fa-circle-check ',
            'class' => 'text-success fw-light'
        ],
        'Unpaid' => [
            'icon'  => 'fa-solid fa-circle-xmark',
            'class' => 'text-danger fw-light'
        ],
        'Refunded' => [
            'icon'  => 'fa-solid fa-arrow-rotate-left',
            'class' => 'text-primary fw-light'
        ],
        'Refund Pending' => [
            'icon'  => 'fa-solid fa-hourglass-start',
            'class' => 'text-warning fw-light'
        ]
    ];

    # reservation - user
    # a reservation belongs to one user
    public function user() {
        return $this->belongsTo(User::class)->withTrashed();
    }


    # reservation - space
    # a reservation belongs to one space
    public function space() {
        return $this->belongsTo(Space::class)->withTrashed();
    }


    # reservation - payment
    # a reservation has one payment
    public function payment() {
        return $this->hasOne(Payment::class)->withTrashed();
    }
}