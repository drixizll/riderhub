<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'rider_id',
        'type',
        'pickup_address',
        'pickup_lat',
        'pickup_lng',
        'dropoff_address',
        'dropoff_lat',
        'dropoff_lng',
        'item_description',
        'fare',
        'promo_code',
        'discount_amount',
        'cancellation_reason',
        'proof_photo_path',
        'payment_method',
        'status',
        'scheduled_at',
    ];

    protected $casts = [
        'pickup_lat' => 'float',
        'pickup_lng' => 'float',
        'dropoff_lat' => 'float',
        'dropoff_lng' => 'float',
        'fare' => 'float',
        'discount_amount' => 'float',
        'scheduled_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function riderLocations(): HasMany
    {
        return $this->hasMany(RiderLocation::class);
    }

    public function rating(): HasOne
    {
        return $this->hasOne(BookingRating::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(BookingMessage::class);
    }

    public function latestLocation(): ?RiderLocation
    {
        return $this->riderLocations()->latest('updated_at')->first();
    }

    public function tripPin(): string
    {
        $hash = abs(crc32("riderhub-trip-{$this->id}-{$this->customer_id}"));

        return str_pad((string) ($hash % 10000), 4, '0', STR_PAD_LEFT);
    }
}
