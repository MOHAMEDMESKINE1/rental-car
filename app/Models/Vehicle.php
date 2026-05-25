<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Vehicle extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'category_id',
        'branch_id',
        'make',
        'model',
        'year',
        'color',
        'plate_number',
        'vin',
        'transmission',
        'fuel_type',
        'seat_count',
        'status',
        'mileage',
        'fuel_level',
        'next_service_date',
        'insurance_expiry',
        'registration_expiry',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'year'                  => 'integer',
        'mileage'               => 'integer',
        'fuel_level'            => 'integer',
        'seat_count'            => 'integer',
        'next_service_date'     => 'date',
        'insurance_expiry'      => 'date',
        'registration_expiry'   => 'date',
        'is_active'             => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos');
        $this->addMediaCollection('thumbnail')->singleFile();
        $this->addMediaCollection('documents');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(300)
            ->performOnCollections('photos', 'thumbnail');
    }

    // ── Relations ──────────────────────────────────────────────
    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'category_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function activeRental(): HasOne
    {
        return $this->hasOne(Rental::class)->where('status', 'active');
    }

    public function damages(): HasMany
    {
        return $this->hasMany(Damage::class);
    }

    public function maintenance(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(VehicleInspection::class);
    }

    // ── Scopes ─────────────────────────────────────────────────
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')->where('is_active', true);
    }

    public function scopeByBranch($query, string $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    // ── Accessors ──────────────────────────────────────────────
    public function getDisplayNameAttribute(): string
    {
        return "{$this->year} {$this->make} {$this->model}";
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->status === 'available' && $this->is_active;
    }

    public function getInsuranceExpiredAttribute(): bool
    {
        return $this->insurance_expiry && $this->insurance_expiry->isPast();
    }

    public function getRegistrationExpiredAttribute(): bool
    {
        return $this->registration_expiry && $this->registration_expiry->isPast();
    }
}
