<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RsvpGuest extends Model
{
    protected $fillable = ['rsvp_id', 'name', 'checked_in_at'];

    protected $casts = ['checked_in_at' => 'datetime'];

    public function rsvp(): BelongsTo
    {
        return $this->belongsTo(Rsvp::class);
    }
}
