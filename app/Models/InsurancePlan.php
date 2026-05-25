<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsurancePlan extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = [
        'name',
        'code',
        'description',
        'coverage_type',
        'price_per_day',
        'deductible',
        'covers_theft',
        'covers_third_party',
        'is_active'
    ];
    protected $casts = [
        'price_per_day' =>  'decimal:2',
        'deductible' => 'decimal:2',
        'covers_theft' => 'boolean',
        'covers_third_party' => 'boolean',
        'is_active' => 'boolean'
    ];
    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }
    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
