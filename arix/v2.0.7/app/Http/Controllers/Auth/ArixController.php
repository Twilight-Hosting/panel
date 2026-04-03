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
            'NONCE' => '0c3a825c378233d11fef031a4d4ec767',
            'ID' => '676416',
            'USERNAME' => 'aghostz',
            'TIMESTAMP' => '1775170452'
        ]);
    }
}