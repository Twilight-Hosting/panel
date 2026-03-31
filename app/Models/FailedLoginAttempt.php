<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Model;

class FailedLoginAttempt extends Model
{
    protected $table = 'failed_login_attempts';

    protected $fillable = [
        'email',
        'ip_address',
        'attempted_at',
        'is_blocked',
        'blocked_until',
    ];

    protected $casts = [
        'attempted_at' => 'datetime',
        'blocked_until' => 'datetime',
        'is_blocked' => 'boolean',
    ];

    public static function recordFailure($email, $ipAddress)
    {
        return static::create([
            'email' => $email,
            'ip_address' => $ipAddress,
            'attempted_at' => now(),
        ]);
    }

    public static function getRecentFailures($email, $ipAddress, $minutes = 5)
    {
        return static::where(function($query) use ($email, $ipAddress) {
            if ($email) {
                $query->where('email', $email);
            }
            if ($ipAddress) {
                $query->orWhere('ip_address', $ipAddress);
            }
        })
        ->where('attempted_at', '>=', now()->subMinutes($minutes))
        ->where(function($query) {
            $query->where('is_blocked', false)
                  ->orWhere('blocked_until', '>', now());
        })
        ->count();
    }

    public static function isBlocked($email, $ipAddress)
    {
        $blocked = static::where(function($query) use ($email, $ipAddress) {
            if ($email) {
                $query->where('email', $email);
            }
            if ($ipAddress) {
                $query->orWhere('ip_address', $ipAddress);
            }
        })
        ->where('is_blocked', true)
        ->where('blocked_until', '>', now())
        ->exists();

        return $blocked;
    }

    public static function block($email, $ipAddress, $minutes)
    {
        static::where(function($query) use ($email, $ipAddress) {
            if ($email) {
                $query->where('email', $email);
            }
            if ($ipAddress) {
                $query->orWhere('ip_address', $ipAddress);
            }
        })
        ->where('attempted_at', '>=', now()->subMinutes($minutes * 2))
        ->update([
            'is_blocked' => true,
            'blocked_until' => now()->addMinutes($minutes),
        ]);
    }

    public static function clearFailures($email, $ipAddress)
    {
        static::where(function($query) use ($email, $ipAddress) {
            if ($email) {
                $query->where('email', $email);
            }
            if ($ipAddress) {
                $query->orWhere('ip_address', $ipAddress);
            }
        })
        ->delete();
    }
}

