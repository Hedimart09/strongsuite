<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberPinToken extends Model
{
    protected $fillable = [
        'member_id',
        'token',
        'pin',
        'expires_at',
        'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'viewed_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isViewed(): bool
    {
        return $this->viewed_at !== null;
    }

    public function markAsViewed(): void
    {
        $this->update(['viewed_at' => now()]);
    }
}
