<?php

namespace Pterodactyl\Http\Controllers\Api\Client\PlayersManagerAddon;

use Illuminate\Http\JsonResponse;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\Http\Requests\Api\Client\Servers\Network\GetNetworkRequest;
use Pterodactyl\Models\Server;
use Pterodactyl\Repositories\Wings\DaemonFileRepository;
use Pterodactyl\Services\Nodes\NodeJWTService;
use Illuminate\Support\Facades\Http;

class PlayersController extends ClientApiController
{
    protected const SUPPORTED_GAMES = [
        // egg_id => game key
        16 => 'scpsl',
    ];

    /**
     * FileController constructor.
     */
    public function __construct(
        private NodeJWTService $jwtService,
        private DaemonFileRepository $fileRepository
    ) {
        parent::__construct();
    }

    /**
     * Lists all the allocations available to a server and whether
     * they are currently assigned as the primary for this server.
     */
    public function index(GetNetworkRequest $request, Server $server): array
    {
        $game = self::SUPPORTED_GAMES[$server->egg_id] ?? null;

        if ($game === null) {
            return $this->unsupported();
        }

        return match ($game) {
            'scpsl' => $this->handleScpsl($server, $request),
            default => $this->unsupported(),
        };
    }

    /**
     * Used when is unsupported
     * @return array
     */
    private function unsupported(): array
    {
        return [
            'success' => false,
            'data' => [
                'message' => 'This egg is not supported',
            ],
        ];
    }

    /**
     * Handles SCP:SL case
     * @param Server $server
     * @return array
     */
    private function handleScpsl(Server $server, GetNetworkRequest $request): array
    {
        $contents = $this->fileRepository
            ->setServer($server)
            ->getDirectory('/.config/SCP Secret Laboratory/LabAPI/plugins/global');

        $found = false;

        foreach ($contents as $content) {
            if($content['name'] !== "PlayerManagerUtils.dll")
                continue;

            $found = true;
            break;
        }

        if(!$found)
                return [
                    'success' => false,
                    'data' => [
                        'message' => 'Plugin not installed',
                    ],
                ];

        $hash = hash('sha256', $this->fileRepository->setServer($server)->getContent(
            '/.config/SCP Secret Laboratory/verkey.txt',
            config('pterodactyl.files.max_edit_size')
        ));

        $params = [
            'token' => $hash,
        ];

        $action = $request->input('action', 'GetPlayers');

        switch($action) {
            case 'Ban':
                $params['id'] = $request->get('id');
                $params['reason'] = $request->get('reason');
                $params['seconds'] = $request->get('seconds');
                break;
        }

        $base_url =
            'http://' .
            $server->allocation->ip .
            ':' .
            $server->allocation->port .
            '/' .
            $action;

        try {
            $response = Http::timeout(3)->get($base_url, $params);
        }
        catch(ConnectionException $e) {
            return [
                'success' => false,
                'data' => [
                    'message' => 'Server is offline',
                ],
            ];
        }

        return $response->json();
    }
}
