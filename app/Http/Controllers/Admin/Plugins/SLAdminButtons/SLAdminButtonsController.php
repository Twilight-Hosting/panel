<?php

namespace Pterodactyl\Http\Controllers\Admin\Plugins\SLAdminButtons;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Models\Server;

class SLAdminButtonsController extends Controller
{
    public const MANAGER_SERVER_ENDPOINT = 'https://api.scpslgame.com/provider/manageserver.php';

    /**
     * SLAdminButtonsController constructor.
     */
    public function __construct(
        private AlertsMessageBag $alert    
    ) {
    }

    /**
     * Reset the verification key for a specific server
     * 
     * @param Server $server
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetVerKey(Server $server)
    {
        if ($server->egg_id != 16) {
            return redirect()->back()->withErrors(['error' => 'This action is only available for SCP: SL servers.']);
        }

        try {
            // Get values from .env file
            $vhpId = env('SECRET_LABORATORY_VHP_ID');
            $vhpKey = env('SECRET_LABORATORY_VHP_KEY');

            if (!isset($vhpId) || !isset($vhpKey)) {
                return redirect()->back()->withErrors(['error' => 'There is no stored VHP Key or ID in .env!']);
            }

            // Get server-specific variables
            $ip = $server->allocation->ip;
            $port = $server->allocation->port;

            // Build your curl command using Laravel's HTTP client
            $response = Http::asForm()->post(self::MANAGER_SERVER_ENDPOINT, [
                'user' => $vhpId,
                'token' => $vhpKey,

                'ip' => $ip,
                'port' => $port,
                'action' => 'resetverkey',
            ]);

            if ($response->successful()) {
                $this->alert->success(trans('admin/server.alerts.ver_key_reset'))->flash();
                return redirect()->back()->with('success', 'Verification key has been reset successfully!');
            } else {
                Log::error('Failed to reset verification key', [
                    'server_id' => $server->id,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

                return redirect()->back()->withErrors(['error' => 'Failed to reset verification key. API returned: ' . $response->status()]);
            }

        } catch (\Exception $e) {
            Log::error('Exception while resetting verification key', [
                'server_id' => $server->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()]);
        }
    }
}