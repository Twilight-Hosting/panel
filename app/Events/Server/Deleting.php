<?php

namespace Pterodactyl\Events\Server;

use Pterodactyl\Events\Event;
use Pterodactyl\Models\Server;
use Illuminate\Queue\SerializesModels;

class Deleting extends Event
{
    use SerializesModels;

    public function sendRequest(string $ip, int $port): bool
    {
        if ($ip == "")
            die("Invalid hashmap result");

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

    private function getIp(string $alias): string
    {
        $aliasMap = array();

        if (array_key_exists($alias, $aliasMap)) {
            return $aliasMap[$alias];
        }
        return "";
    }

    private function tryRemoveToServerList(Server $server): bool
    {
        $node = $server->allocation->ip_alias;
        $port = $server->allocation->port;
        $ip = $this->getIp($node);
        return $this->sendRequest($ip, $port);
    }

    /**
     * Create a new event instance.
     */
    public function __construct(public Server $server)
    {
        if ($server->nest_id == 6)
            $this->tryRemoveToServerList($server);
    }
}
