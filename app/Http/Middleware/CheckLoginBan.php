<?php

namespace Pterodactyl\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Pterodactyl\Models\Ban;
use Pterodactyl\Models\Block;
use Pterodactyl\Models\FailedLoginAttempt;
use Pterodactyl\Models\ModerationSettings;
use Pterodactyl\Models\User;
use Pterodactyl\Services\ModerationLoggingService;

class CheckLoginBan
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->isMethod('post') || !str_contains($request->path(), 'login')) {
            return $next($request);
        }

        $settings = ModerationSettings::getSettings();

        if (!$settings->block_on_ban) {
            return $next($request);
        }

        $username = $request->input('user') ?? $request->input('email');
        $ipAddress = $request->ip();

        if ($username) {
            $field = str_contains($username, '@') ? 'email' : 'username';
            $user = User::where($field, $username)->first();

            if ($user) {
                $ban = Ban::where('user_id', $user->id)
                    ->where('is_active', true)
                    ->first();

                if ($ban && !$ban->isExpired()) {
                    $errorMessage = 'Your account has been banned. Reason: ' . $ban->reason;
                    
                    ModerationLoggingService::log(
                        null,
                        'login_blocked_ban',
                        'user',
                        $user->id,
                        "Login blocked for banned user {$user->email} ({$user->username})",
                        $ban->reason,
                        $ipAddress
                    );

                    if ($request->expectsJson()) {
                        return response()->json([
                            'errors' => [
                                'user' => [$errorMessage]
                            ]
                        ], 403);
                    }

                    session()->flash('ban_error', true);
                    session()->flash('ban_reason', $ban->reason);
                    
                    return redirect()->back()->withErrors([
                        'user' => $errorMessage,
                    ])->withInput($request->except('password'));
                }

                $block = Block::where('user_id', $user->id)
                    ->where('is_active', true)
                    ->first();

                if ($block && !$block->isExpired()) {
                    $errorMessage = 'Your account has been blocked. Reason: ' . $block->reason;
                    
                    ModerationLoggingService::log(
                        null,
                        'login_blocked_block',
                        'user',
                        $user->id,
                        "Login blocked for blocked user {$user->email} ({$user->username})",
                        $block->reason,
                        $ipAddress
                    );

                    if ($request->expectsJson()) {
                        return response()->json([
                            'errors' => [
                                'user' => [$errorMessage]
                            ]
                        ], 403);
                    }

                    session()->flash('block_error', true);
                    session()->flash('block_reason', $block->reason);
                    
                    return redirect()->back()->withErrors([
                        'user' => $errorMessage,
                    ])->withInput($request->except('password'));
                }
            }
        }

        if (FailedLoginAttempt::isBlocked($username, $ipAddress)) {
            $settings = ModerationSettings::getSettings();
            $lockoutDuration = $settings->lockout_duration_minutes ?? 5;
            $errorMessage = "Too many failed login attempts. Please try again in {$lockoutDuration} minutes.";
            
            if ($request->expectsJson()) {
                return response()->json([
                    'errors' => [
                        'user' => [$errorMessage]
                    ]
                ], 429);
            }
            
            return redirect()->back()->withErrors([
                'user' => $errorMessage,
            ])->withInput($request->except('password'));
        }

        return $next($request);
    }
}

