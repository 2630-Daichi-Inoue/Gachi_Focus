<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Support\Facades\Auth;

class Space extends Model
{
    use SoftDeletes, HasFactory, HasUlids;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'name',
        'prefecture',
        'city',
        'address_line',
        'capacity',
        'open_time',
        'close_time',
        'weekday_price_yen',
        'weekend_price_yen',
        'description',
        'image_path',
        'is_public',
    ];

    protected $appends = [
        'full_address',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'capacity' => 'integer',
        'weekday_price_yen' => 'integer',
        'weekend_price_yen' => 'integer',
        'is_public' => 'boolean',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    public function amenities()
    {
        return $this->belongsToMany(
            Amenity::class,
            'amenity_space',
            'space_id',
            'amenity_id'
        );
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'related');
    }

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'favorites',
            'space_id',
            'user_id'
        );
    }

    public function reviews()
    {
        return $this->hasManyThrough(
            Review::class,
            Reservation::class,
            'space_id',
            'reservation_id',
            'id',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopePublic(Builder $query)
    {
        return $query->where('is_public', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    public function isPublic(): bool
    {
        return $this->is_public && !$this->trashed();
    }

    public function isFavorite(): bool
    {
        $isFavorite = Favorite::where('space_id', $this->id)
                                ->where('user_id', Auth::id())
                                ->exists();
        if ($isFavorite) {
            return true;
        } else {
            return false;
        }
    }

    public function getUnitPriceForDate(CarbonInterface $date): int
    {
        return $date->isWeekend()
            ? $this->weekend_price_yen
            : $this->weekday_price_yen;
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */
    public function getOpenTimeForFormAttribute()
    {
        $openTime = $this->attributes['open_time'] ?? null;
        return Carbon::createFromFormat('H:i:s', $openTime)->format('H:i');
    }

    public function getCloseTimeForFormAttribute()
    {
        $closeTime = $this->attributes['close_time'] ?? null;
        return Carbon::createFromFormat('H:i:s', $closeTime)->format('H:i');
    }

    public function getFullAddressAttribute(): string
    {
        return "{$this->address_line}, {$this->city}, {$this->prefecture}";
    }
}
