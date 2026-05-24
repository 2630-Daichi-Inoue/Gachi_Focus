<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUlids, Notifiable, SoftDeletes;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'name',
        'is_admin',
        'email',
        'password',
        'phone',
        'avatar_path',
        'user_status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting.
     */
    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favoriteSpaces()
    {
        return $this->belongsToMany(
            Space::class,
            'favorites',
            'user_id',
            'space_id'
        );
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function isRestricted(): bool
    {
        return $this->user_status === 'restricted' && ! $this->trashed();
    }

    public function isBanned(): bool
    {
        return $this->user_status === 'banned' && ! $this->trashed();
    }

    public function isDeleted(): bool
    {
        return $this->trashed();
    }
}
