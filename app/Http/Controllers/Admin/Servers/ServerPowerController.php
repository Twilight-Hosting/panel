<?php

namespace Pterodactyl\Http\Controllers\Admin\Servers;

use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Models\Server;
use Pterodactyl\Repositories\Wings\DaemonPowerRepository;
use Pterodactyl\Facades\Activity;

class ServerPowerController extends Controller
{
    private DaemonPowerRepository $repository;

    public function __construct(DaemonPowerRepository $repository)
    {
        $this->repository = $repository;
    }

    public function start(Server $server)
    {
        $this->repository->setServer($server)->send('start');
        Activity::event('server:power.start')->log();
        return redirect()->back()->with('status', 'Server started successfully!');
    }

    public function stop(Server $server)
    {
        $this->repository->setServer($server)->send('stop');
        Activity::event('server:power.stop')->log();
        return redirect()->back()->with('status', 'Server stopped successfully!');
    }

    public function restart(Server $server)
    {
        $this->repository->setServer($server)->send('restart');
        Activity::event('server:power.restart')->log();
        return redirect()->back()->with('status', 'Server restarted successfully!');
    }

    public function kill(Server $server)
    {
        $this->repository->setServer($server)->send('kill');
        Activity::event('server:power.kill')->log();
        return redirect()->back()->with('status', 'Server killed successfully!');
    }
}
