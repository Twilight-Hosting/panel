<?php

namespace Pterodactyl\Policies;

use Pterodactyl\Models\User;
use Pterodactyl\Models\Server;

class ServerPolicy
{
    /**
     * Checks if the user has the given permission on/for the server.
     */
    protected function checkPermission(User $user, Server $server, string $permission): bool
    {
        if (empty($permission)) {
            return false;
        }

        $subuser = $server->subusers->where('user_id', $user->id)->first();

        if ($subuser) {
            if (in_array($permission, $subuser->permissions)) {
                return true;
            }
        }

        if ($role = $user->role()) {
            if (!in_array($server->id, $role->excluded_servers)) {
                if ($role->hasPermission($permission)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Runs before any of the functions are called. Used to determine if user is root admin, if so, ignore permissions.
     */
    public function before(User $user, string $ability, Server $server): bool
    {
        if ($user->root_admin || $server->owner_id === $user->id) {
            return true;
        }

        return $this->checkPermission($user, $server, $ability);
    }

    /**
     * This is a horrendous hack to avoid Laravel's "smart" behavior that does
     * not call the before() function if there isn't a function matching the
     * policy permission.
     */
    public function __call(string $name, mixed $arguments)
    {
        // do nothing
    }
}
