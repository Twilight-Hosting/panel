<?php

namespace Pterodactyl\Http\Controllers\Api\Client\SLPlugins;

use Illuminate\Auth\Access\AuthorizationException;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Models\Permission;
use Pterodactyl\Models\Plugins\InstalledSLPlugins;
use Pterodactyl\Models\Server;
use Pterodactyl\Repositories\Wings\DaemonFileRepository;

class SLPluginsController extends ClientApiController
{
    private const CACHE_FILE = 'slplugins_cache.json';
    private const CACHE_DURATION = 15 * 60;
    private const PLUGINS_API_URL = 'https://plugins.scpslgame.com/api/v1/plugin?search=&limit=10000000';

    public function __construct(private DaemonFileRepository $daemonFileRepository)
    {
        parent::__construct();
    }

    public function getService(Server $_) {
        try {
            $queryParameters = request()->query();

            if (!isset($queryParameters['framework'])) {
                return response()->json([
                    'error' => 'Missing framework parameter',
                ], 400);
            }
            elseif(!isset($queryParameters['page'])) {
                $queryParameters['page'] = 1;
            }

            if ($queryParameters['framework'] === 'labapi') {
                $plugins = $this->getCache();
                
                if (!isset($plugins['data']['data']))
                {
                    return response()->json([
                        'error' => 'Failed to acquire LabAPI plugins',
                        'data' => $plugins
                    ], 503);
                }

                $response = $plugins['data']['data'];
                if (isset($queryParameters['search']) && $queryParameters['search'] !== '')
                {
                    $search = $queryParameters['search'];
                    $response = array_filter($response, function($entry) use ($search) {
                        return stripos($entry['name'], $search);
                    });
                }

                if (isset($queryParameters['tags']))
                {
                    $tags = $queryParameters['tags'];
                    $response = array_filter($response, function($entry) use ($tags) {
                        $slugs = array_column($entry['tags'], 'slug');
                        return empty(array_diff($tags, $slugs));
                    });
                }

                usort($response, function($a, $b) {
                    return $b['downloads'] - $a['downloads'];
                });

                $count = count($response);
                $response = array_slice($response, ($queryParameters['page'] - 1) * 20, 20);

                $response = array_map(function($plugin) {
                    $plugin['framework'] = 'labapi';
                    return $plugin;
                }, $response);

                return response()->json([
                    'data' => $response,
                    'meta' => ['pagination' => [
                        'total' => $count,
                        'count' => count($response),
                        'per_page' => 20,
                        'current_page' => $queryParameters['page'],
                        'total_pages' => ceil($count / 20)]
                    ],
                ]);
            }

            return response()->json([
                'error' => 'Unsupported framework',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'error' => [$e->getMessage(), $e->getTraceAsString()]
            ], 400);
        }
    }

    public function getInstalled(Server $server) {
        $request = Request();

        if (!$request->user()->can(Permission::ACTION_FILE_READ, $server)) {
            throw new AuthorizationException();
        }

        $installedPlugins = InstalledSLPlugins::where('server_id', $server->id)->get();

        return response()->json($installedPlugins);
    }

    public function store(Server $server)
    {
        try {
            $request = Request();

            if (!$request->user()->can(Permission::ACTION_FILE_CREATE, $server)) {
                throw new AuthorizationException();
            }

            $request->validate([
                'plugin_framework' => 'required|string',
                'plugin_version' => 'required|string',
                'plugin_id' => 'required|string',
                'plugin_name' => 'required|string',
                'plugin_icon' => 'required|string',
                'file_names' => 'required|string[]',
            ]);

            $framework = $request->plugin_framework;
            $plugin_id = $request->plugin_id;
            $name = $request->plugin_name;
            $file_names = $request->file_names;

            if ($framework === 'exiled' & !$this->hasExiled($server)) {
                return response()->json([
                    'error' => 'You cannot install an EXILED plugin without EXILED!',
                ], 400);
            }

            // check if plugin is already installed
            $installedPlugin = InstalledSLPlugins::where('server_id', $server->id)->where('plugin_id', $plugin_id)->first();

            if ($installedPlugin) {
                return response()->json([
                    'error' => 'Plugin already installed',
                ], 400);
            }

            // store the plugin
            $installedPlugin = InstalledSLPlugins::create([
                'plugin_framework' => $framework,
                'plugin_id' => $plugin_id,
                'plugin_name' => $name,
                'plugin_icon' => $request->plugin_icon,
                'server_id' => $server->id,
                'file_names' => $file_names,
            ]);

            return response()->json([
                'message' => 'Plugin installed',
                'id' => $installedPlugin->id,
                'plugin_framework' => $framework,
                'plugin_id' => $plugin_id,
                'plugin_name' => $name,
                'server_id' => $server->id,
                'file_names' => $file_names,
                'plugin_icon' => $request->plugin_icon,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => [$e->getMessage(), $e->getTraceAsString()]]);
        }
    }

    public static function tryRemovePlugin(string $path, int $server_id, array|string $files)
    {
        # using $path, check $files for anything that might be in a plugins folder, then check DB using $server_id
    }

    private function getCache()
    {
        if ($this->isCacheValid())
            return $this->getCachedData();
        else
            return $this->fetchAndCachePluginsData();
    }

    private function isCacheValid(): bool {
        if (!file_exists($this->getCacheFilePath())) {
            return false;
        }

        $fileModTime = filemtime($this->getCacheFilePath());
        $currentTime = time();

        return ($currentTime - $fileModTime) < self::CACHE_DURATION;
    }

    private function getCachedData() {
        if (!file_exists($this->getCacheFilePath())) {
            return [
                'error' => 'Cache file not found',
            ];
        }

        $cachedContent = file_get_contents($this->getCacheFilePath());
        $data = json_decode($cachedContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'error' => 'Failed to parse cached data',
            ];
        }

        return $data;
    }

    private function fetchAndCachePluginsData() {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => self::PLUGINS_API_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        if ($response === false) {
            return [
                'error' => 'Failed to fetch data from plugins API: ' . $curlError,
            ];
        }

        if ($httpCode !== 200) {
            return [
                'error' => 'Plugins API returned HTTP ' . $httpCode,
            ];
        }

        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'error' => 'Failed to parse response from plugins API',
            ];
        }

        if (!isset($data['data']['meta'])) {
            return [
                'error' => 'Invalid response structure from plugins API',
            ];
        }

        $this->saveToCache($data);
        
        return $data;
    }

    private function saveToCache(array $data): bool {
        try {
            $jsonData = json_encode($data, JSON_PRETTY_PRINT);
            if ($jsonData === false) {
                return false;
            }
            
            $result = file_put_contents($this->getCacheFilePath(), $jsonData, LOCK_EX);
            return $result !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getCacheFilePath(): string
    {
        return storage_path('app/' . self::CACHE_FILE);
    }

    private function getPlugin($pluginId)
    {
        $plugins = $this->getCache();
        $response = array_first($plugins['data']['data'], function($entry) use ($pluginId) {
            return $entry['id'] === $pluginId;
        });

        return $response;
    }

    private function hasEXILED(Server $server): bool
    {
        $this->daemonFileRepository->setServer($server);
        return !empty($this->daemonFileRepository->getDirectory('/home/container/.config/EXILED'));
    }
}