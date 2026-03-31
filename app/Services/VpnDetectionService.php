<?php

namespace Pterodactyl\Services;

class VpnDetectionService
{
    public static function isVpn($ipAddress)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://ip-api.com/json/{$ipAddress}?fields=status,message,proxy,hosting");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || !$response) {
                return false;
            }

            $data = json_decode($response, true);

            if (isset($data['status']) && $data['status'] === 'success') {
                if (isset($data['proxy']) && $data['proxy'] === true) {
                    return true;
                }
                if (isset($data['hosting']) && $data['hosting'] === true) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}

