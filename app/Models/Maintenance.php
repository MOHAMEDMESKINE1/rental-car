<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Maintenance extends Model implements HasMedia
{
    use HasUuids, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'type',
        'title',
        'description',
        'cost',
        'scheduled_date',
        'completed_date',
        'mileage_at_service',
        'status',
        'performed_by',
        'garage_name',
        'notes',
    ];

    protected $casts = [
        'cost'              => 'decimal:2',
        'scheduled_date'    => 'date',
        'completed_date'    => 'date',
        'mileage_at_service' => 'integer',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('invoices');
        $this->addMediaCollection('photos');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
    public function scopeScheduled($q)
    {
        return $q->where('status', 'scheduled');
    }
    public function scopeCompleted($q)
    {
        return $q->where('status', 'completed');
    }
}
