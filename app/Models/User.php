<?php


namespace App\Models;


// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasUlids;

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
            'is_admin'          => 'boolean',
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
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
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeActive($query)
    {
        return $query->where('user_status', 'active')
                    ->whereNull('deleted_at');
    }

    public function scopeRestricted($query)
    {
        return $query->where('user_status', 'restricted')
                    ->whereNull('deleted_at');
    }

    public function scopeBanned($query)
    {
        return $query->where('user_status', 'banned')
                    ->whereNull('deleted_at');
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

    public function isActive(): bool
    {
        return $this->user_status === 'active' && is_null($this->deleted_at);
    }

    public function isRestricted(): bool
    {
        return $this->user_status === 'restricted' && is_null($this->deleted_at);
    }

    public function isBanned(): bool
    {
        return $this->user_status === 'banned' && is_null($this->deleted_at);
    }

    public function isDeleted(): bool
    {
        return !is_null($this->deleted_at);
    }
}
