<?php

namespace Pterodactyl\Services\AktiCubeDevelopmentTeam;

use Pterodactyl\Contracts\Repository\AktiCubeDevelopmentTeamPermissionManagerRoleRepositoryInterface;

class PermissionManagerRoleCreationService
{
    protected AktiCubeDevelopmentTeamPermissionManagerRoleRepositoryInterface $repository;

    public function __construct(AktiCubeDevelopmentTeamPermissionManagerRoleRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function handle(array $data)
    {
        return $this->repository->create($data, true, true);
    }
}
