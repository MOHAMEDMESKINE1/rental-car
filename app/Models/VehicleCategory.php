<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class VehicleCategory extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'seat_count',
        'luggage_count',
        'transmission',
        'base_price_per_day',
        'extra_km_price',
        'free_km_per_day',
        'is_active',
    ];

    protected $casts = [
        'base_price_per_day' => 'decimal:2',
        'extra_km_price'     => 'decimal:2',
        'is_active'          => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail')->singleFile();
    }

    // ── Relations ──────────────────────────────────────────────
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'category_id');
    }

    // ── Scopes ─────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
