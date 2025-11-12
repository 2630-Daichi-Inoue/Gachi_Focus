<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Space extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'name',
        'location_for_overview',
        'location_for_details',
        'min_capacity',
        'max_capacity',
        'area',
        'weekday_price',
        'weekend_price',
        'description',
        'image', // fallback image path or URL
        // 'address', // add here only if the column exists
    ];

    /**
     * Type casting
     */
    protected $casts = [
        'min_capacity'  => 'integer',
        'max_capacity'  => 'integer',
        'weekday_price' => 'decimal:2',
        'weekend_price' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // space - category_space (a space has many categories)
    public function categorySpace()
    {
        return $this->hasMany(CategorySpace::class);
    }

    // space - review
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // space - reservation
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    // space - photo
    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors / Domain Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * ✅ Display image with fallback.
     * Priority: 1) first photo -> 2) image column -> 3) no-image.png
     * Works with URLs, data:image, public/images, or storage paths.
     */
    public function getDisplayImageUrlAttribute(): string
    {
        // Get the first photo path (avoid N+1 with subquery)
        $path = $this->photos()->value('path') ?: $this->image;

        // No image at all → default
        if (!$path) {
            return asset('images/no-image.png');
        }

        // Already a full URL or data:image
        if (Str::startsWith($path, ['http://', 'https://', 'data:image'])) {
            return $path;
        }

        // public/images or storage/... inside /public
        if (Str::startsWith($path, ['images/', 'storage/'])) {
            return asset($path);
        }

        // Otherwise treat as storage/app/public relative path
        return asset('storage/' . ltrim($path, '/'));
    }

    /**
     * 🌍 Detect country code (used by Pricing/Tax logic)
     */
    public function getCountryCodeAttribute(): string
    {
        $loc = strtolower($this->location_for_details ?? '');

        return match (true) {
            str_contains($loc, 'japan'),
            str_contains($loc, 'tokyo'),
            str_contains($loc, 'osaka') => 'JP',

            str_contains($loc, 'france'),
            str_contains($loc, 'paris') => 'FR',

            str_contains($loc, 'united states'),
            str_contains($loc, 'usa'),
            str_contains($loc, 'new york') => 'US',

            str_contains($loc, 'australia'),
            str_contains($loc, 'sydney') => 'AU',

            str_contains($loc, 'singapore') => 'SG',

            default => 'JP',
        };
    }
}
