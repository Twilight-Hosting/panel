<?php

namespace Pterodactyl\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Pterodactyl\Models\FailedLoginAttempt;
use Pterodactyl\Models\LoginHistory;
use Pterodactyl\Models\ModerationSettings;
use Pterodactyl\Services\ModerationLoggingService;

class TrackLoginAttempts
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $settings = ModerationSettings::getSettings();
        $email = $request->input('email');
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        if ($request->isMethod('post') && ($request->route()->getName() === 'auth.login' || str_contains($request->path(), 'login'))) {
            if (\Auth::check()) {
                $user = \Auth::user();
                if ($settings->track_login_history) {
                    LoginHistory::recordLogin($user, $ipAddress, $userAgent, true);
                }
                
                ModerationLoggingService::log(
                    $user->id,
                    'login_success',
                    'user',
                    $user->id,
                    "User {$user->email} ({$user->username}) logged in successfully",
                    null,
                    $ipAddress
                );

                FailedLoginAttempt::clearFailures($email, $ipAddress);
            } else {
                if ($email) {
                    $user = \Pterodactyl\Models\User::where('email', $email)->first();
                    
                    FailedLoginAttempt::recordFailure($email, $ipAddress);
                    
                    if ($settings->track_login_history) {
                        LoginHistory::recordLogin(null, $ipAddress, $userAgent, false, 'Invalid credentials');
                    }

                    ModerationLoggingService::log(
                        null,
                        'login_failed',
                        'user',
                        $user ? $user->id : null,
                        "Failed login attempt for email: {$email}",
                        'Invalid credentials',
                        $ipAddress
                    );

                    $recentFailures = FailedLoginAttempt::getRecentFailures($email, $ipAddress, $settings->lockout_duration_minutes ?? 5);
                    $maxAttempts = 5;

                    if ($recentFailures >= $maxAttempts) {
                        FailedLoginAttempt::block($email, $ipAddress, $settings->lockout_duration_minutes ?? 5);
                        
                        ModerationLoggingService::log(
                            null,
                            'login_blocked',
                            $email ? 'email' : 'ip',
                            null,
                            "Login blocked after {$maxAttempts} failed attempts for email: {$email} / IP: {$ipAddress}",
                            "Too many failed login attempts",
                            $ipAddress
                        );
                    }
                }
            }
        }

        return $response;
    }
}

