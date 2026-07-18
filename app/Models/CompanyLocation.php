<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyLocation extends Model
{
    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'radius',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'radius' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the active company location.
     * Returns the first active location or null.
     */
    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Get the active location settings as an array.
     * Falls back to config values if no active location in DB.
     */
    public static function getSettings(): array
    {
        $location = static::getActive();

        if ($location) {
            return [
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'radius' => $location->radius,
                'name' => $location->name,
                'address' => $location->address,
            ];
        }

        // Fallback to config/env values
        return [
            'latitude' => (float) config('app.company_latitude', -7.842498796390817),
            'longitude' => (float) config('app.company_longitude', 113.44212290165065),
            'radius' => (int) config('app.geofence_radius', 50),
            'name' => 'Toko Utama',
            'address' => null,
        ];
    }
}
