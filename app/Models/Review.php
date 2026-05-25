<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasUuids;

    protected $fillable = [
        'customer_id',
        'rental_id',
        'rating',
        'vehicle_rating',
        'service_rating',
        'comment',
        'is_published',
    ];

    protected $casts = [
        'rating'         => 'integer',
        'vehicle_rating' => 'integer',
        'service_rating' => 'integer',
        'is_published'   => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }
}
