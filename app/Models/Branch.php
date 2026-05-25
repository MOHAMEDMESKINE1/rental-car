<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Branch extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'country',
        'phone',
        'email',
        'latitude',
        'longitude',
        'opening_time',
        'closing_time',
        'is_active',
    ];

    protected $casts = [
        'latitude'    => 'decimal:7',
        'longitude'   => 'decimal:7',
        'is_active'   => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile();
    }

    // ── Relations ──────────────────────────────────────────────
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function pickupReservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'pickup_branch_id');
    }

    public function dropoffReservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'dropoff_branch_id');
    }

    public function pickupRentals(): HasMany
    {
        return $this->hasMany(Rental::class, 'pickup_branch_id');
    }

    // ── Scopes ─────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
