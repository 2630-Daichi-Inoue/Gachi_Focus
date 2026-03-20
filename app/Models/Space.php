<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Carbon\CarbonInterface;

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

    public function favoritedUsers()
    {
        return $this->belongsToMany(
            User::class,
            'favorites',
            'space_id',
            'user_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopePublic($query)
    {
        return $query->where('is_public', true)
                    ->whereNull('deleted_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    public function isPublic(): bool
    {
        return $this->is_public && is_null($this->deleted_at);
    }

    public function isWithinBusinessHours(CarbonInterface $startAt, CarbonInterface $endAt): bool
    {
        $startTime = $startAt->format('H:i:s');
        $endTime = $endAt->format('H:i:s');

        return $startTime >= $this->open_time
            && $endTime <= $this->close_time;
    }

    public function fullAddress(): string
    {
        return "{$this->prefecture}{$this->city}{$this->address_line}";
    }

    public function unitPriceForDate(CarbonInterface $date): int
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
        $open_time = $this->attributes['open_time'] ?? null;
        return Carbon::createFromFormat('H:i:s', $open_time)->format('H:i');
    }

    public function getCloseTimeForFormAttribute()
    {
        $close_time = $this->attributes['close_time'] ?? null;
        return Carbon::createFromFormat('H:i:s', $close_time)->format('H:i');
    }
}
