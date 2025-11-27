<?php

namespace App\Services\Server;

use Pterodactyl\Models\Server;
use Illuminate\Support\Facades\Log;
use Pterodactyl\Repositories\Wings\DaemonFileRepository;

class DirectoryService
{
    public function __construct(
        private DaemonFileRepository $daemonServerRepository)
    {}

    public function hasEXILED(Server $server): bool
    {
        return $this->daemonServerRepository->setServer($server)->getDirectory('/home/container/.config/EXILED');
    }
}