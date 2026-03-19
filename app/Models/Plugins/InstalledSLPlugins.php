<?php

namespace Pterodactyl\Models\Plugins;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstalledSLPlugins extends Model {
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'scpsl_installed_plugins';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'plugin_framework',
        'plugin_version',
        'plugin_id',
        'server_id',
        'plugin_name',
        'plugin_icon',
        'files'
    ];

    protected $casts = [
        'files' => 'array',
    ];
}