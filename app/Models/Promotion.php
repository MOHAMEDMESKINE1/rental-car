<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promotion extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'value',
        'min_rental_days',
        'max_discount',
        'valid_from',
        'valid_until',
        'max_uses',
        'used_count',
        'is_active',
    ];

    protected $casts = [
        'value'          => 'decimal:2',
        'max_discount'   => 'decimal:2',
        'valid_from'     => 'date',
        'valid_until'    => 'date',
        'is_active'      => 'boolean',
    ];

    public function isValid(): bool
    {
        if (! $this->is_active) return false;
        if (now()->lt($this->valid_from) || now()->gt($this->valid_until)) return false;
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) return false;
        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        if ($this->type === 'percentage') {
            $discount = $amount * ($this->value / 100);
            if ($this->max_discount) {
                $discount = min($discount, (float) $this->max_discount);
            }
            return round($discount, 2);
        }
        return min((float) $this->value, $amount);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function scopeValid($query)
    {
        return $query->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now());
    }
}
