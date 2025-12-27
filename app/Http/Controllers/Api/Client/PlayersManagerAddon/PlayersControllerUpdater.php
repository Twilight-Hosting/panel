<?php

namespace Pterodactyl\Http\Controllers\Api\Client\PlayersManagerAddon;

use Illuminate\Http\JsonResponse;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\Http\Requests\Api\Client\Servers\Network\GetNetworkRequest;
use Pterodactyl\Models\Server;
use Pterodactyl\Repositories\Wings\DaemonFileRepository;
use Pterodactyl\Services\Nodes\NodeJWTService;
use Illuminate\Support\Facades\Storage;

class PlayersControllerUpdater extends ClientApiController
{

    // if you are reading this you are confused asf on where tf this file is let me help you out :3
    // pterofolder/storage/app/
    // - Zer0Two :3
    protected const SUPPORTED_GAMES = [
        // egg_id => local filepath
        16 => ['scpsl', 'plugins/scpsl/PlayerManagerUtils.dll'],
    ];

    /**
     * FileController constructor.
     */
    public function __construct(
        private NodeJWTService       $jwtService,
        private DaemonFileRepository $fileRepository
    )
    {
        parent::__construct();
    }

    /**
     * Lists all the allocations available to a server and whether
     * they are currently assigned as the primary for this server.
     */
    public function index(GetNetworkRequest $request, Server $server): array
    {
        $gameData = self::SUPPORTED_GAMES[$server->egg_id] ?? null;

        if ($gameData === null) {
            return $this->unsupported();
        }

        $action = $request->query('action');

        if (!in_array($action, ['update', 'install'])) {
            return [
                'success' => false,
                'data' => [
                    'message' => 'Invalid action. Use ?action=update or ?action=install',
                ],
            ];
        }

        [$gameName, $pluginPath] = $gameData;

        return match ($gameName) {
            'scpsl' => $this->handleScpsl($server, $request, $action, $pluginPath),
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

    private function handleScpsl(Server $server, GetNetworkRequest $request, string $action, string $pluginPath): array
    {
        try {
            if (!Storage::disk('local')->exists($pluginPath)) {
                return [
                    'success' => false,
                    'data' => [
                        'message' => 'Local plugin file not found at: ' . $pluginPath,
                    ],
                ];
            }

            $latestContent = Storage::disk('local')->get($pluginPath);
            $latestVersion = md5($latestContent);

            $pluginFilename = basename($pluginPath);

            $serverPluginDirectory = '/.config/SCP Secret Laboratory/LabAPI/plugins/global';

            $contents = $this->fileRepository
                ->setServer($server)
                ->getDirectory($serverPluginDirectory);

            $found = false;
            $currentVersion = null;

            foreach ($contents as $content) {
                if ($content['name'] !== $pluginFilename)
                    continue;

                $found = true;

                $currentFileContent = $this->fileRepository
                    ->setServer($server)
                    ->getContent($serverPluginDirectory . '/' . $pluginFilename);

                $currentVersion = md5($currentFileContent);
                break;
            }

            if ($action === 'update') {
                if (!$found) {
                    return [
                        'success' => false,
                        'data' => [
                            'message' => 'Plugin not installed. Use ?action=install to install it.',
                        ],
                    ];
                }

                if ($currentVersion === $latestVersion) {
                    return [
                        'success' => true,
                        'data' => [
                            'message' => 'Plugin is already up to date',
                            'action' => 'none',
                            'version' => substr($currentVersion, 0, 8),
                        ],
                    ];
                }

                $this->fileRepository
                    ->setServer($server)
                    ->putContent(
                        $serverPluginDirectory . '/' . $pluginFilename,
                        $latestContent
                    );

                Activity::event('server:file.uploaded')
                    ->property('file', $pluginFilename)
                    ->property('directory', $serverPluginDirectory)
                    ->log();

                return [
                    'success' => true,
                    'data' => [
                        'message' => 'Plugin updated successfully',
                        'action' => 'updated',
                        'old_version' => substr($currentVersion, 0, 8),
                        'new_version' => substr($latestVersion, 0, 8),
                    ],
                ];
            }

            // Handle INSTALL action
            if ($action === 'install') {
                // If plugin exists and is up to date
                if ($found && $currentVersion === $latestVersion) {
                    return [
                        'success' => true,
                        'data' => [
                            'message' => 'Plugin is already installed and up to date',
                            'action' => 'none',
                            'version' => substr($currentVersion, 0, 8),
                        ],
                    ];
                }

                // Install or update the plugin
                $this->fileRepository
                    ->setServer($server)
                    ->putContent(
                        $serverPluginDirectory . '/' . $pluginFilename,
                        $latestContent
                    );

                if ($found) {
                    Activity::event('server:file.uploaded')
                        ->property('file', $pluginFilename)
                        ->property('directory', $serverPluginDirectory)
                        ->log();

                    return [
                        'success' => true,
                        'data' => [
                            'message' => 'Plugin updated to latest version',
                            'action' => 'updated',
                            'old_version' => substr($currentVersion, 0, 8),
                            'new_version' => substr($latestVersion, 0, 8),
                        ],
                    ];
                } else {
                    Activity::event('server:file.uploaded')
                        ->property('file', $pluginFilename)
                        ->property('directory', $serverPluginDirectory)
                        ->log();

                    return [
                        'success' => true,
                        'data' => [
                            'message' => 'Plugin installed successfully',
                            'action' => 'installed',
                            'version' => substr($latestVersion, 0, 8),
                        ],
                    ];
                }
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => [
                    'message' => 'Error: ' . $e->getMessage(),
                ],
            ];
        }

        return [
            'success' => false,
            'data' => [
                'message' => 'Unknown error',
            ],
        ];
    }
}
