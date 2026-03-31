<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Model;
use Pterodactyl\Models\User;

class Warning extends Model
{
    protected $table = 'warnings';

    protected $fillable = [
        'user_id',
        'warned_by',
        'reason',
        'warned_at',
    ];

    protected $casts = [
        'warned_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function warnedBy()
    {
        return $this->belongsTo(User::class, 'warned_by');
    }
}

