<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdditionalDriver extends Model
{
    use HasUuids;
    protected $fillable = [
        'rental_id',
        'full_name',
        'license_number',
        'license_country',
        'license_expiry',
        'date_of_birth',
        'daily_fee'
    ];
    protected $casts = [
        'license_expiry' => 'date',
        'date_of_birth' => 'date',
        'daily_fee' => 'decimal:2'
    ];
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }
}
