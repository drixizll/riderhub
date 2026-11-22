<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'gender',
        'municipality',
        'profile_photo_path',
        'is_online',
        'rider_status',
        'vehicle_model',
        'plate_number',
        'license_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_online' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasActiveBooking(): bool
    {
        return $this->riderBookings()->whereIn('status', ['accepted', 'picked_up'])->exists();
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'customer_id');
    }

    public function riderBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'rider_id');
    }

    public function ratingsReceived(): HasMany
    {
        return $this->hasMany(BookingRating::class, 'rider_id');
    }

    public function ratingsGiven(): HasMany
    {
        return $this->hasMany(BookingRating::class, 'customer_id');
    }
}
