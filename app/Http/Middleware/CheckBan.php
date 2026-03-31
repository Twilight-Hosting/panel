<?php

namespace Pterodactyl\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Pterodactyl\Models\Ban;
use Pterodactyl\Models\Block;
use Pterodactyl\Models\User;

class CheckBan
{
    public function handle(Request $request, Closure $next)
    {
        $ipAddress = $request->ip();

        if ($ipAddress) {
            $ipBan = Ban::where('ip_address', $ipAddress)
                ->where('ip_ban', true)
                ->where('is_active', true)
                ->first();

            if ($ipBan && !$ipBan->isExpired()) {
                $ipBanReason = $ipBan->reason;
                $errorMessage = 'Your IP address has been banned. Reason: ' . $ipBanReason;
                
                if (auth()->check()) {
                    auth()->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'errors' => [
                            'ip' => [$errorMessage]
                        ]
                    ], 403);
                }
                
                return redirect()->route('auth.login')->withErrors([
                    'user' => $errorMessage,
                ]);
            }

            if ($ipBan && $ipBan->isExpired()) {
                $ipBan->is_active = false;
                $ipBan->save();
            }
        }

        if (auth()->check()) {
            $user = auth()->user();
            
            $ban = Ban::where('user_id', $user->id)
                ->where('is_active', true)
                ->first();

            if ($ban && !$ban->isExpired()) {
                $banReason = $ban->reason;
                $errorMessage = 'Your account has been banned. Reason: ' . $banReason;
                
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'errors' => [
                            'user' => [$errorMessage]
                        ]
                    ], 403);
                }
                
                return redirect()->route('auth.login')->withErrors([
                    'user' => $errorMessage,
                ]);
            }

            if ($ban && $ban->isExpired()) {
                $ban->is_active = false;
                $ban->save();
            }

            $block = Block::where('user_id', $user->id)
                ->where('is_active', true)
                ->first();

            if ($block && !$block->isExpired()) {
                $blockReason = $block->reason;
                $errorMessage = 'Your account has been blocked. Reason: ' . $blockReason;
                
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'errors' => [
                            'user' => [$errorMessage]
                        ]
                    ], 403);
                }
                
                return redirect()->route('auth.login')->withErrors([
                    'user' => $errorMessage,
                ]);
            }

            if ($block && $block->isExpired()) {
                $block->is_active = false;
                $block->save();
            }
        }

        return $next($request);
    }
}

