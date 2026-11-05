<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rsvp extends Model
{
    protected $fillable = ['invitation_id', 'attending', 'guest_count', 'dietary', 'message'];

    protected $casts = ['attending' => 'boolean'];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(RsvpGuest::class);
    }
}
