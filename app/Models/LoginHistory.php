<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Model;
use Pterodactyl\Models\User;

class LoginHistory extends Model
{
    protected $table = 'login_history';

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'successful',
        'failure_reason',
        'logged_in_at',
    ];

    protected $casts = [
        'successful' => 'boolean',
        'logged_in_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function recordLogin($user, $ipAddress, $userAgent, $successful = true, $failureReason = null)
    {
        return static::create([
            'user_id' => $user ? $user->id : null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'successful' => $successful,
            'failure_reason' => $failureReason,
            'logged_in_at' => now(),
        ]);
    }
}

