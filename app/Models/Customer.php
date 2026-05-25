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

class Customer extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'city',
        'country',
        'nationality',
        'license_number',
        'license_country',
        'license_expiry',
        'passport_number',
        'id_card_number',
        'date_of_birth',
        'gender',
        'is_blacklisted',
        'blacklist_reason',
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'date_of_birth'  => 'date',
        'is_blacklisted' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('license_front')->singleFile();
        $this->addMediaCollection('license_back')->singleFile();
        $this->addMediaCollection('passport')->singleFile();
        $this->addMediaCollection('id_card')->singleFile();
        $this->addMediaCollection('avatar')->singleFile();
    }

    // ── Relations ──────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function activeRental(): HasOne
    {
        return $this->hasOne(Rental::class)->where('status', 'active');
    }

    // ── Scopes ─────────────────────────────────────────────────
    public function scopeNotBlacklisted($query)
    {
        return $query->where('is_blacklisted', false);
    }

    // ── Accessors ──────────────────────────────────────────────
    public function getFullNameAttribute(): string
    {
        return $this->user->name;
    }

    public function getLicenseExpiredAttribute(): bool
    {
        return $this->license_expiry && $this->license_expiry->isPast();
    }
}
