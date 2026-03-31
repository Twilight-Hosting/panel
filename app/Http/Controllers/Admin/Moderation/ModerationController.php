<?php

namespace Pterodactyl\Http\Controllers\Admin\Moderation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Models\Ban;
use Pterodactyl\Models\Warning;
use Pterodactyl\Models\Block;
use Pterodactyl\Models\ModerationSettings;
use Pterodactyl\Models\ModerationLog;
use Pterodactyl\Models\FailedLoginAttempt;
use Pterodactyl\Models\LoginHistory;
use Pterodactyl\Models\User;
use Pterodactyl\Services\VpnDetectionService;
use Pterodactyl\Services\ModerationLoggingService;
use Pterodactyl\Http\Controllers\Controller;

class ModerationController extends Controller
{
    public function __construct(
        protected AlertsMessageBag $alert,
    ) {}

    public function index(Request $request)
    {
        $bans = Ban::with(['user', 'bannedBy'])
            ->where('is_active', true)
            ->orderBy('banned_at', 'desc')
            ->get();

        $warnings = Warning::with(['user', 'warnedBy'])
            ->orderBy('warned_at', 'desc')
            ->limit(50)
            ->get();

        $blocks = Block::with(['user', 'blockedBy'])
            ->where('is_active', true)
            ->orderBy('blocked_at', 'desc')
            ->get();

        $settings = ModerationSettings::getSettings();

        $query = ModerationLog::with(['actionBy', 'user'])->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->input('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%')
                  ->orWhere('reason', 'like', '%' . $search . '%')
                  ->orWhere('action_type', 'like', '%' . $search . '%');
            });
        }

        if ($request->has('action_type') && $request->input('action_type')) {
            $query->where('action_type', $request->input('action_type'));
        }

        if ($request->has('date_from') && $request->input('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to') && $request->input('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to') . ' 23:59:59');
        }

        $logs = $query->paginate(50)->withQueryString();

        $actionTypes = ModerationLog::select('action_type')->distinct()->pluck('action_type');

        return view('admin.moderation.index', [
            'bans' => $bans,
            'warnings' => $warnings,
            'blocks' => $blocks,
            'settings' => $settings,
            'logs' => $logs,
            'actionTypes' => $actionTypes,
        ]);
    }

    public function create()
    {
        return view('admin.moderation.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'required|string|max:1000',
            'ip_ban' => 'boolean',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $user = User::findOrFail($request->input('user_id'));

        if ($user->root_admin) {
            $this->alert->danger('Cannot ban root administrators.')->flash();
            return redirect()->back()->withInput();
        }

        $existingBan = Ban::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if ($existingBan) {
            $this->alert->danger('User is already banned.')->flash();
            return redirect()->back()->withInput();
        }

        $ipAddress = null;
        $ipBan = false;

        if ($request->input('ip_ban')) {
            $ipAddress = $user->last_ip ?? null;
            
            if ($ipAddress && VpnDetectionService::isVpn($ipAddress)) {
                $this->alert->warning('Cannot ban user IP address because it is a VPN or proxy. User will be banned but IP will not be banned.')->flash();
                $ipBan = false;
                $ipAddress = null;
            } else {
                $ipBan = true;
            }
        }

        $ban = Ban::create([
            'user_id' => $user->id,
            'banned_by' => auth()->id(),
            'reason' => $request->input('reason'),
            'ip_address' => $ipAddress,
            'ip_ban' => $ipBan,
            'banned_at' => now(),
            'expires_at' => $request->input('expires_at') ? \Carbon\Carbon::parse($request->input('expires_at')) : null,
            'is_active' => true,
        ]);

        $this->logoutUser($user);

        $this->alert->success('User banned successfully.')->flash();

        return redirect()->route('admin.moderation');
    }

    public function warnForm()
    {
        return view('admin.moderation.warn');
    }

    public function warn(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'required|string|max:1000',
        ]);

        $user = User::findOrFail($request->input('user_id'));

        if ($user->root_admin) {
            $this->alert->danger('Cannot warn root administrators.')->flash();
            return redirect()->back()->withInput();
        }

        Warning::create([
            'user_id' => $user->id,
            'warned_by' => auth()->id(),
            'reason' => $request->input('reason'),
            'warned_at' => now(),
        ]);

        ModerationLoggingService::log(
            auth()->id(),
            'warn',
            'user',
            $user->id,
            "User {$user->email} ({$user->username}) has been warned",
            $request->input('reason'),
            $request->ip()
        );

        $this->alert->success('User warned successfully.')->flash();

        return redirect()->route('admin.moderation');
    }

    public function blockForm()
    {
        return view('admin.moderation.block');
    }

    public function block(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'required|string|max:1000',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $user = User::findOrFail($request->input('user_id'));

        if ($user->root_admin) {
            $this->alert->danger('Cannot block root administrators.')->flash();
            return redirect()->back()->withInput();
        }

        $existingBlock = Block::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if ($existingBlock) {
            $this->alert->danger('User is already blocked.')->flash();
            return redirect()->back()->withInput();
        }

        Block::create([
            'user_id' => $user->id,
            'blocked_by' => auth()->id(),
            'reason' => $request->input('reason'),
            'blocked_at' => now(),
            'expires_at' => $request->input('expires_at') ? \Carbon\Carbon::parse($request->input('expires_at')) : null,
            'is_active' => true,
        ]);

        $this->logoutUser($user);

        ModerationLoggingService::log(
            auth()->id(),
            'block',
            'user',
            $user->id,
            "User {$user->email} ({$user->username}) has been blocked",
            $request->input('reason'),
            $request->ip()
        );

        $this->alert->success('User blocked successfully.')->flash();

        return redirect()->route('admin.moderation');
    }

    public function unblock(Block $block)
    {
        $block->is_active = false;
        $block->save();

        ModerationLoggingService::log(
            auth()->id(),
            'unblock',
            'user',
            $block->user_id,
            "User {$block->user->email} ({$block->user->username}) has been unblocked",
            null,
            $request->ip()
        );

        $this->alert->success('User unblocked successfully.')->flash();

        return redirect()->route('admin.moderation');
    }

    public function ipBanForm()
    {
        return view('admin.moderation.ip-ban');
    }

    public function ipBan(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'required|string|max:1000',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $ipAddress = $request->input('ip_address');

        if (VpnDetectionService::isVpn($ipAddress)) {
            $this->alert->danger('Cannot ban VPN or proxy IP addresses.')->flash();
            return redirect()->back()->withInput();
        }

        $existingBan = Ban::where('ip_address', $ipAddress)
            ->where('ip_ban', true)
            ->where('is_active', true)
            ->first();

        if ($existingBan) {
            $this->alert->danger('IP address is already banned.')->flash();
            return redirect()->back()->withInput();
        }

        Ban::create([
            'user_id' => 0,
            'banned_by' => auth()->id(),
            'reason' => $request->input('reason'),
            'ip_address' => $ipAddress,
            'ip_ban' => true,
            'banned_at' => now(),
            'expires_at' => $request->input('expires_at') ? \Carbon\Carbon::parse($request->input('expires_at')) : null,
            'is_active' => true,
        ]);

        ModerationLoggingService::log(
            auth()->id(),
            'ip_ban',
            'ip',
            null,
            "IP address {$ipAddress} has been banned",
            $request->input('reason'),
            $ipAddress
        );

        $this->alert->success('IP address banned successfully.')->flash();

        return redirect()->route('admin.moderation');
    }

    public function forceLogout($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->root_admin) {
            $this->alert->danger('Cannot force logout root administrators.')->flash();
            return redirect()->back();
        }

        $this->logoutUser($user);

        ModerationLoggingService::log(
            auth()->id(),
            'force_logout',
            'user',
            $user->id,
            "User {$user->email} ({$user->username}) has been force logged out",
            null,
            $request->ip()
        );

        $this->alert->success('User logged out successfully.')->flash();

        return redirect()->route('admin.moderation');
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'vpn_blocker_enabled' => 'boolean',
            'discord_webhook_enabled' => 'boolean',
            'discord_webhook_url' => 'nullable|url',
            'lockout_duration_minutes' => 'required|integer|min:1|max:60',
            'track_login_history' => 'boolean',
            'block_on_ban' => 'boolean',
        ]);

        $settings = ModerationSettings::getSettings();
        $settings->update([
            'vpn_blocker_enabled' => $request->input('vpn_blocker_enabled', false),
            'discord_webhook_enabled' => $request->input('discord_webhook_enabled', false),
            'discord_webhook_url' => $request->input('discord_webhook_url'),
            'lockout_duration_minutes' => $request->input('lockout_duration_minutes', 5),
            'track_login_history' => $request->input('track_login_history', true),
            'block_on_ban' => $request->input('block_on_ban', true),
        ]);

        $this->alert->success('Settings updated successfully.')->flash();

        return redirect()->route('admin.moderation');
    }

    public function loginHistory()
    {
        $history = LoginHistory::with('user')
            ->orderBy('logged_in_at', 'desc')
            ->paginate(50);

        return view('admin.moderation.login-history', [
            'history' => $history,
        ]);
    }

    public function failedAttempts()
    {
        $attempts = FailedLoginAttempt::orderBy('attempted_at', 'desc')
            ->paginate(50);

        $blockedCount = FailedLoginAttempt::where('is_blocked', true)
            ->where('blocked_until', '>', now())
            ->count();

        return view('admin.moderation.failed-attempts', [
            'attempts' => $attempts,
            'blockedCount' => $blockedCount,
        ]);
    }

    public function clearFailedAttempts()
    {
        FailedLoginAttempt::where('attempted_at', '<', now()->subHours(24))->delete();

        $this->alert->success('Old failed login attempts cleared successfully.')->flash();

        return redirect()->route('admin.moderation.failed-attempts');
    }

    public function unblockFailedAttempt($id)
    {
        $attempt = FailedLoginAttempt::findOrFail($id);
        $attempt->is_blocked = false;
        $attempt->blocked_until = null;
        $attempt->save();

        $this->alert->success('Failed login attempt unblocked successfully.')->flash();

        return redirect()->route('admin.moderation.failed-attempts');
    }

    public function unban(Request $request, Ban $ban)
    {
        $ban->is_active = false;
        $ban->save();

        $targetUser = $ban->user;
        $targetDescription = $ban->ip_ban 
            ? "IP address {$ban->ip_address} has been unbanned"
            : "User {$targetUser->email} ({$targetUser->username}) has been unbanned";

        ModerationLoggingService::log(
            auth()->id(),
            'unban',
            $ban->ip_ban ? 'ip' : 'user',
            $ban->ip_ban ? null : $ban->user_id,
            $targetDescription,
            null,
            $request->ip()
        );

        $this->alert->success('User unbanned successfully.')->flash();

        return redirect()->route('admin.moderation');
    }

    public function destroy(Request $request, Ban $ban)
    {
        $targetUser = $ban->user;
        $targetDescription = $ban->ip_ban 
            ? "IP address {$ban->ip_address} ban deleted"
            : "User {$targetUser->email} ({$targetUser->username}) ban deleted";

        ModerationLoggingService::log(
            auth()->id(),
            'ban_deleted',
            $ban->ip_ban ? 'ip' : 'user',
            $ban->ip_ban ? null : $ban->user_id,
            $targetDescription,
            null,
            $request->ip()
        );

        $ban->delete();

        $this->alert->success('Ban record deleted successfully.')->flash();

        return redirect()->route('admin.moderation');
    }

    public function destroyWarning(Request $request, Warning $warning)
    {
        $targetUser = $warning->user;

        ModerationLoggingService::log(
            auth()->id(),
            'warning_deleted',
            'user',
            $warning->user_id,
            "Warning for user {$targetUser->email} ({$targetUser->username}) deleted",
            null,
            $request->ip()
        );

        $warning->delete();

        $this->alert->success('Warning deleted successfully.')->flash();

        return redirect()->route('admin.moderation');
    }

    public function destroyBlock(Request $request, Block $block)
    {
        $targetUser = $block->user;

        ModerationLoggingService::log(
            auth()->id(),
            'block_deleted',
            'user',
            $block->user_id,
            "Block for user {$targetUser->email} ({$targetUser->username}) deleted",
            null,
            $request->ip()
        );

        $block->delete();

        $this->alert->success('Block record deleted successfully.')->flash();

        return redirect()->route('admin.moderation');
    }

    public function searchUsers(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where('email', 'like', '%' . $query . '%')
            ->orWhere('username', 'like', '%' . $query . '%')
            ->where('root_admin', false)
            ->limit(10)
            ->get(['id', 'email', 'username']);

        return response()->json($users);
    }

    private function logoutUser($user)
    {
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();
    }
}

