<?php

namespace Pterodactyl\Events\Server;

use Pterodactyl\Events\Event;
use Pterodactyl\Models\Server;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class Deleting extends Event
{
    use SerializesModels;

    public function sendRequest(string $ip, int $port): bool
    {
        try {
            $url = 'https://api.scpslgame.com/provider/manageserver.php';

            $data = array(
                'user' => config('secretLaboratory.vhp_user_id'),
                'token' => config('secretLaboratory.vhp_key'),
                'ip' => $ip,
                'port' => $port,
                'action' => 'reset'
            );

            if ($data["user"] == '')
                die("Fuck shit fuck n--");

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($curl);
            $file = fopen("/var/www/pterodactyl/storage/logs/deleting.log", "a");
            fwrite($file, date("Y-m-d h:m:s", time()) . "\n");
            fwrite($file, "$ip\n");
            fwrite($file, "$port\n");
            fwrite($file, "$result\n");
            fclose($file);

            return $result == "OK";
        }
        catch (\Exception $e)
        {
            Log::error('Failed to register server under the VHP', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    private function tryRemoveToServerList(Server $server): bool
    {
        $ip = $server->allocation->ip;
        $port = $server->allocation->port;
        return $this->sendRequest($ip, $port);
    }

    /**
     * Create a new event instance.
     */
    public function __construct(public Server $server)
    {
        if ($server->egg_id == 16)
            $this->tryRemoveToServerList($server);
    }
}
