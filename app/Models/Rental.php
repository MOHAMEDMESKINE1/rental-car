<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Rental extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'rental_number',
        'reservation_id',
        'customer_id',
        'vehicle_id',
        'pickup_branch_id',
        'dropoff_branch_id',
        'insurance_plan_id',
        'promotion_id',
        'planned_pickup_at',
        'planned_dropoff_at',
        'actual_pickup_at',
        'actual_dropoff_at',
        'pickup_mileage',
        'dropoff_mileage',
        'pickup_fuel_level',
        'dropoff_fuel_level',
        'base_amount',
        'insurance_amount',
        'extras_amount',
        'discount_amount',
        'extra_km_charges',
        'late_return_charges',
        'damage_charges',
        'fuel_charges',
        'total_amount',
        'status',
        'deposit_amount',
        'deposit_returned',
        'notes',
        'agent_id',
    ];

    protected $casts = [
        'planned_pickup_at'   => 'datetime',
        'planned_dropoff_at'  => 'datetime',
        'actual_pickup_at'    => 'datetime',
        'actual_dropoff_at'   => 'datetime',
        'base_amount'         => 'decimal:2',
        'insurance_amount'    => 'decimal:2',
        'extras_amount'       => 'decimal:2',
        'discount_amount'     => 'decimal:2',
        'extra_km_charges'    => 'decimal:2',
        'late_return_charges' => 'decimal:2',
        'damage_charges'      => 'decimal:2',
        'fuel_charges'        => 'decimal:2',
        'total_amount'        => 'decimal:2',
        'deposit_amount'      => 'decimal:2',
        'deposit_returned'    => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $model) {
            if (empty($model->rental_number)) {
                $model->rental_number = 'RNT-' . strtoupper(substr(uniqid(), -8));
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('contract')->singleFile();
        $this->addMediaCollection('documents');
    }

    // ── Relations ──────────────────────────────────────────────
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
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
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
    public function additionalDrivers(): HasMany
    {
        return $this->hasMany(AdditionalDriver::class);
    }
    public function inspections(): HasMany
    {
        return $this->hasMany(VehicleInspection::class);
    }
    public function damages(): HasMany
    {
        return $this->hasMany(Damage::class);
    }
    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function extraServices(): BelongsToMany
    {
        return $this->belongsToMany(ExtraService::class, 'rental_extra_services')
            ->withPivot('quantity', 'unit_price')
            ->withTimestamps();
    }

    // ── Accessors ──────────────────────────────────────────────
    public function getPlannedDaysAttribute(): int
    {
        return (int) $this->planned_pickup_at->diffInDays($this->planned_dropoff_at) ?: 1;
    }

    public function getActualDaysAttribute(): int
    {
        if (! $this->actual_pickup_at || ! $this->actual_dropoff_at) {
            return $this->planned_days;
        }

        return (int) $this->actual_pickup_at->diffInDays($this->actual_dropoff_at) ?: 1;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'active' && now()->greaterThan($this->planned_dropoff_at);
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->where('status', 'completed')->sum('amount');
    }

    public function getBalanceDueAttribute(): float
    {
        return (float) $this->total_amount - $this->total_paid;
    }

    // ── Scopes ─────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
    public function scopeOverdue($query)
    {
        return $query->where('status', 'active')
            ->where('planned_dropoff_at', '<', now());
    }
}
