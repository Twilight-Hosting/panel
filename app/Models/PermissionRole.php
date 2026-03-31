<?php

namespace Pterodactyl\Models;

use Illuminate\Routing\Route;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $color
 * @property array $permissions
 * @property array $admin_routes
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 */
class PermissionRole extends Model
{
    /**
     * The resource name for this model when it is transformed into an
     * API representation using fractal.
     */
    public const RESOURCE_NAME = 'permission_manager_role';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'permission_manager_roles';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Cast values to correct type.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'color' => 'string',
        'permissions' => 'array',
        'admin_routes' => 'array',
        'excluded_servers' => 'array',
    ];

    /**
     * Fields that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'color',
        'permissions',
        'admin_routes',
        'excluded_servers',
    ];

    /**
     * @var array
     */
    public static array $validationRules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'color' => 'required|regex:/^#[a-f0-9]{6}$/i',
        'permissions' => 'nullable|array',
        'admin_routes' => 'nullable|array',
    ];

    /**
     * Default values for specific columns that are generally not changed on base installations.
     *
     * @var array
     */
    protected $attributes = [];

    public function getNumberOfPermissions(): int
    {
        return count($this->permissions ?? [])  + count($this->admin_routes ?? []);
    }

    /**
     * Returns the hex color for the text to be displayed on top of the color.
     */
    public function getComplementaryColorAttribute(): string
    {
        $color = $this->color;
        $color = str_replace('#', '', $color);
        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));
        $luminance = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
        return $luminance > 128 ? '#000000' : '#ffffff';
    }

    /**
     * Returns the number of users having this role.
     */
    public function getNumberOfUsersHavingThisRole(): int
    {
        return User::query()->where('role_id', $this->id)->count();
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    public function isRouteAllowed(Route $route): bool
    {
        foreach ($route->methods() as $method) {
            if (in_array(str_replace('/', '.', $route->uri) . "|" . $method, $this->admin_routes ?? [])) {
                return true;
            }
        }
        return false;
    }

    public function isRouteStringAllowed(string $route): bool
    {
        return in_array($route, $this->admin_routes ?? []);
    }
}
