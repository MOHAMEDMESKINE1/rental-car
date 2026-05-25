<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Invoice extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia;
    protected $fillable = [
        'invoice_number',
        'rental_id',
        'customer_id',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total',
        'status',
        'due_date',
        'notes',
        'sent_at',
        'paid_at'
    ];
    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime'
    ];
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('pdf')->singleFile();
    }
    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn($m) => $m->invoice_number ??= 'INV-' . date('Ym') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT));
    }
}
