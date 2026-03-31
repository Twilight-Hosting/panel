<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Model;
use Pterodactyl\Models\User;

class Ban extends Model
{
    protected $table = 'bans';

    protected $fillable = [
        'user_id',
        'banned_by',
        'reason',
        'ip_address',
        'ip_ban',
        'banned_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'banned_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bannedBy()
    {
        return $this->belongsTo(User::class, 'banned_by');
    }

    public function isExpired()
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }
}

