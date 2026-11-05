<?php

namespace App\Models;

use App\Enums\GuestCategory;
use App\Services\EventSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'phone', 'email', 'category',
        'max_attendees', 'status', 'responded_at', 'revoked_at',
    ];

    protected $casts = [
        'category' => GuestCategory::class,
        'responded_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Invitation $i) {
            $i->max_attendees ??= app(EventSettings::class)->maxFor($i->category);
        });
    }

    public function rsvp(): HasOne
    {
        return $this->hasOne(Rsvp::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->whereNull('invitations.revoked_at');
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }
}
