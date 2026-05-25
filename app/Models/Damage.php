<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Damage extends Model implements HasMedia
{
    use HasUuids, InteractsWithMedia;

    protected $fillable = [
        'rental_id',
        'vehicle_id',
        'description',
        'location',
        'severity',
        'repair_cost',
        'customer_liable',
        'is_repaired',
        'repaired_at',
        'notes',
        'reported_by',
    ];

    protected $casts = [
        'repair_cost'    => 'decimal:2',
        'customer_liable' => 'boolean',
        'is_repaired'    => 'boolean',
        'repaired_at'    => 'date',
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
    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
