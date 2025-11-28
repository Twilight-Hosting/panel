<?php

namespace App\Services\Server;

use Pterodactyl\Models\Server;
use Pterodactyl\Repositories\Wings\DaemonFileRepository;

class DirectoryService
{
    public function __construct(private DaemonFileRepository $daemonServerRepository)
    {}

    public function hasEXILED(Server $server): bool
    {
        $this->daemonServerRepository->setServer($server);
        return !empty($this->daemonServerRepository->getDirectory('/home/container/.config/EXILED'));
    }
}