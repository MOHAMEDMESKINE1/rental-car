<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ExtraService extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'price',
        'max_quantity',
        'is_active',
    ];

    protected $casts = [
        'price'       => 'decimal:2',
        'max_quantity' => 'integer',
        'is_active'   => 'boolean',
    ];

    public function rentals(): BelongsToMany
    {
        return $this->belongsToMany(Rental::class, 'rental_extra_services')
            ->withPivot('quantity', 'unit_price')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
