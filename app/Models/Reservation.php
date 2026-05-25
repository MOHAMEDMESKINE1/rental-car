<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'reservation_number',
        'customer_id',
        'vehicle_id',
        'pickup_branch_id',
        'dropoff_branch_id',
        'insurance_plan_id',
        'promotion_id',
        'pickup_date',
        'dropoff_date',
        'base_amount',
        'insurance_amount',
        'extras_amount',
        'discount_amount',
        'total_amount',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'pickup_date'      => 'datetime',
        'dropoff_date'     => 'datetime',
        'base_amount'      => 'decimal:2',
        'insurance_amount' => 'decimal:2',
        'extras_amount'    => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'total_amount'     => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $model) {
            if (empty($model->reservation_number)) {
                $model->reservation_number = 'RES-' . strtoupper(substr(uniqid(), -8));
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
    public function pickupBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'pickup_branch_id');
    }
    public function dropoffBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'dropoff_branch_id');
    }
    public function insurancePlan(): BelongsTo
    {
        return $this->belongsTo(InsurancePlan::class);
    }
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function rental(): HasOne
    {
        return $this->hasOne(Rental::class);
    }

    public function extraServices(): BelongsToMany
    {
        return $this->belongsToMany(ExtraService::class, 'reservation_extra_services')
            ->withPivot('quantity', 'unit_price')
            ->withTimestamps();
    }

    public function getDaysCountAttribute(): int
    {
        return (int) $this->pickup_date->diffInDays($this->dropoff_date) ?: 1;
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }
}
