<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Model;
use Pterodactyl\Models\User;

class ModerationLog extends Model
{
    protected $table = 'moderation_logs';

    protected $fillable = [
        'user_id',
        'action_by',
        'action_type',
        'target_type',
        'target_id',
        'description',
        'reason',
        'ip_address',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function actionBy()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}

