<?php

namespace Pterodactyl\Http\Controllers\Auth;

use Illuminate\Support\Facades\Http;

class ArixController extends AbstractLoginController
{
    public function index(): object
    {

        $endpoint = 'https://api.arix.gg/resource/arix-pterodactyl/verify';
    
        $response = Http::asForm()->post($endpoint, [
            'license' => 'ARIX-CHECK',
        ]);
    
        $responseData = $response->json();
    
        if (!$responseData['success']) {
            return response()->json([
                'status' => 'Not available'
            ]);
        }

        return response()->json([
            'NONCE' => 'add476291c3b7bc97fdcea5389afb157',
            'ID' => '676416',
            'USERNAME' => 'aghostz',
            'TIMESTAMP' => '1762840763'
        ]);
    }
}