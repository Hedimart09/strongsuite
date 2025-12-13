<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model implements AuthenticatableContract
{
    /** @use HasFactory<\Database\Factories\MemberFactory> */
    use Authenticatable, HasFactory;

    protected $fillable = [
        'user_id',
        'member_id',
        'name',
        'email',
        'phone',
        'photo',
        'date_of_birth',
        'gender',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'qr_code',
        'pin',
        'status',
    ];

    protected $hidden = [
        'pin',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function activeSubscription(): HasMany
    {
        return $this->subscriptions()->where('status', 'active')
            ->where('end_date', '>=', now());
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getAuthPassword(): string
    {
        return $this->pin;
    }
}
