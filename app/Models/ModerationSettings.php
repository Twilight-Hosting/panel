<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Model;

class ModerationSettings extends Model
{
    protected $table = 'moderation_settings';

    protected $fillable = [
        'vpn_blocker_enabled',
        'discord_webhook_url',
        'discord_webhook_enabled',
        'max_failed_attempts',
        'lockout_duration_minutes',
        'track_login_history',
        'block_on_ban',
    ];

    protected $casts = [
        'vpn_blocker_enabled' => 'boolean',
        'discord_webhook_enabled' => 'boolean',
        'track_login_history' => 'boolean',
        'block_on_ban' => 'boolean',
    ];

    public static function getSettings()
    {
        return static::first() ?? static::create([
            'vpn_blocker_enabled' => false,
            'discord_webhook_enabled' => false,
            'discord_webhook_url' => null,
            'max_failed_attempts' => 5,
            'lockout_duration_minutes' => 5,
            'track_login_history' => true,
            'block_on_ban' => true,
        ]);
    }
}

