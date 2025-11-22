<?php

namespace Pterodactyl\Repositories\Eloquent;

use Pterodactyl\Contracts\Repository\AktiCubeDevelopmentTeamPermissionManagerRoleRepositoryInterface;
use Pterodactyl\Models\PermissionRole;

class AktiCubeDevelopmentTeamPermissionManagerRoleRepository extends EloquentRepository implements AktiCubeDevelopmentTeamPermissionManagerRoleRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function model(): string
    {
        return PermissionRole::class;
    }
}
