<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class VehicleInspection extends Model implements HasMedia
{
    use HasUuids, InteractsWithMedia;

    protected $fillable = [
        'rental_id',
        'vehicle_id',
        'type',
        'condition_front',
        'condition_back',
        'condition_left',
        'condition_right',
        'condition_interior',
        'fuel_level',
        'mileage',
        'spare_tire',
        'jack_tool',
        'first_aid_kit',
        'fire_extinguisher',
        'notes',
        'inspected_by',
        'inspected_at',
        'customer_signature',
    ];

    protected $casts = [
        'inspected_at'     => 'datetime',
        'spare_tire'       => 'boolean',
        'jack_tool'        => 'boolean',
        'first_aid_kit'    => 'boolean',
        'fire_extinguisher' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos');
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }
}
