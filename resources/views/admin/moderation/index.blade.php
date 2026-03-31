@extends('layouts.admin')

@section('title')
Moderation
@endsection

@section('content-header')
<h1>
    Moderation
    <small>Manage user bans, warnings, blocks, and settings.</small>
</h1>
<ol class="breadcrumb">
    <li><a href="{{ route('admin.index') }}">Admin</a></li>
    <li class="active">Moderation</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="nav-tabs-custom" style="background: transparent; border: none; box-shadow: none;">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#bans" data-toggle="tab">Bans</a></li>
                <li><a href="#warnings" data-toggle="tab">Warnings</a></li>
                <li><a href="#blocks" data-toggle="tab">Blocks</a></li>
                <li><a href="#logs" data-toggle="tab">Logs</a></li>
                <li><a href="#settings" data-toggle="tab">Settings</a></li>
            </ul>
            <div class="tab-content" style="padding: 0;">
                <div class="tab-pane active" id="bans">
                    <div class="box box-danger">
                        <div class="box-header with-border">
                            <h3 class="box-title">Active Bans</h3>
                            <div class="box-tools">
                                <a href="{{ route('admin.moderation.create') }}" class="btn btn-sm btn-danger">
                                    <i class="fa fa-ban"></i> Ban User
                                </a>
                                <a href="{{ route('admin.moderation.ip-ban') }}" class="btn btn-sm btn-warning">
                                    <i class="fa fa-globe"></i> IP Ban
                                </a>
                            </div>
                        </div>
                        <div class="box-body table-responsive no-padding">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User / IP</th>
                                        <th>Banned By</th>
                                        <th>Reason</th>
                                        <th>Banned At</th>
                                        <th>Expires At</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bans as $ban)
                                    <tr>
                                        <td>{{ $ban->id }}</td>
                                        <td>
                                            @if($ban->ip_ban)
                                                <strong>{{ $ban->ip_address }}</strong>
                                                <br><small class="text-muted">IP Ban</small>
                                            @else
                                                <strong>{{ $ban->user->email ?? 'N/A' }}</strong>
                                                <br><small class="text-muted">{{ $ban->user->username ?? 'N/A' }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $ban->bannedBy->email ?? 'Unknown' }}</td>
                                        <td>{{ Str::limit($ban->reason, 50) }}</td>
                                        <td>{{ $ban->banned_at ? $ban->banned_at->format('Y-m-d H:i') : 'N/A' }}</td>
                                        <td>
                                            @if($ban->expires_at)
                                                {{ $ban->expires_at->format('Y-m-d H:i') }}
                                            @else
                                                <span class="label label-danger">Permanent</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ban->ip_ban)
                                                <span class="label label-warning">IP Ban</span>
                                            @else
                                                <span class="label label-danger">User Ban</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ban->isExpired())
                                                <span class="label label-warning">Expired</span>
                                            @else
                                                <span class="label label-danger">Active</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.moderation.unban', $ban) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-xs btn-success">
                                                    <i class="fa fa-check"></i> Unban
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.moderation.destroy', $ban) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure?');">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <p class="text-muted">No active bans found.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="warnings">
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title">Recent Warnings</h3>
                            <div class="box-tools">
                                <a href="{{ route('admin.moderation.warn') }}" class="btn btn-sm btn-warning">
                                    <i class="fa fa-exclamation-triangle"></i> Warn User
                                </a>
                            </div>
                        </div>
                        <div class="box-body table-responsive no-padding">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Warned By</th>
                                        <th>Reason</th>
                                        <th>Warned At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($warnings as $warning)
                                    <tr>
                                        <td>{{ $warning->id }}</td>
                                        <td>
                                            <strong>{{ $warning->user->email }}</strong>
                                            <br><small class="text-muted">{{ $warning->user->username }}</small>
                                        </td>
                                        <td>{{ $warning->warnedBy->email ?? 'Unknown' }}</td>
                                        <td>{{ Str::limit($warning->reason, 50) }}</td>
                                        <td>{{ $warning->warned_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <form action="{{ route('admin.moderation.warning.destroy', $warning) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure?');">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <p class="text-muted">No warnings found.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="blocks">
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title">Active Blocks</h3>
                            <div class="box-tools">
                                <a href="{{ route('admin.moderation.block') }}" class="btn btn-sm btn-info">
                                    <i class="fa fa-lock"></i> Block User
                                </a>
                            </div>
                        </div>
                        <div class="box-body table-responsive no-padding">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Blocked By</th>
                                        <th>Reason</th>
                                        <th>Blocked At</th>
                                        <th>Expires At</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($blocks as $block)
                                    <tr>
                                        <td>{{ $block->id }}</td>
                                        <td>
                                            <strong>{{ $block->user->email }}</strong>
                                            <br><small class="text-muted">{{ $block->user->username }}</small>
                                        </td>
                                        <td>{{ $block->blockedBy->email ?? 'Unknown' }}</td>
                                        <td>{{ Str::limit($block->reason, 50) }}</td>
                                        <td>{{ $block->blocked_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            @if($block->expires_at)
                                                {{ $block->expires_at->format('Y-m-d H:i') }}
                                            @else
                                                <span class="label label-danger">Permanent</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($block->isExpired())
                                                <span class="label label-warning">Expired</span>
                                            @else
                                                <span class="label label-info">Active</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.moderation.unblock', $block) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-xs btn-success">
                                                    <i class="fa fa-unlock"></i> Unblock
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.moderation.block.destroy', $block) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure?');">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <p class="text-muted">No active blocks found.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="logs">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Moderation Logs</h3>
                        </div>
                        <div class="box-body">
                            <form method="GET" action="{{ route('admin.moderation') }}" class="mb-3">
                                <div class="row">
                                    <div class="col-md-3">
                                        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <select name="action_type" class="form-control">
                                            <option value="">All Actions</option>
                                            @foreach($actionTypes as $type)
                                                <option value="{{ $type }}" {{ request('action_type') == $type ? 'selected' : '' }}>
                                                    {{ ucfirst(str_replace('_', ' ', $type)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="From Date">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="To Date">
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                        <a href="{{ route('admin.moderation') }}" class="btn btn-default">Reset</a>
                                    </div>
                                </div>
                            </form>
                            <div class="table-responsive no-padding">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Action</th>
                                            <th>Action By</th>
                                            <th>Target</th>
                                            <th>Description</th>
                                            <th>Reason</th>
                                            <th>IP</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($logs as $log)
                                        <tr>
                                            <td>{{ $log->id }}</td>
                                            <td>
                                                <span class="label label-info">{{ ucfirst(str_replace('_', ' ', $log->action_type)) }}</span>
                                            </td>
                                            <td>
                                                {{ $log->actionBy->email ?? 'Unknown' }}
                                                <br><small class="text-muted">{{ $log->actionBy->username ?? '' }}</small>
                                            </td>
                                            <td>
                                                @if($log->user)
                                                    {{ $log->user->email }}
                                                    <br><small class="text-muted">{{ $log->user->username }}</small>
                                                @elseif($log->ip_address)
                                                    {{ $log->ip_address }}
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>{{ Str::limit($log->description, 50) }}</td>
                                            <td>{{ $log->reason ? Str::limit($log->reason, 30) : '-' }}</td>
                                            <td>{{ $log->ip_address ?? '-' }}</td>
                                            <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center">
                                                <p class="text-muted">No logs found.</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="box-footer">
                                {{ $logs->links() }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="settings">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Moderation Settings</h3>
                        </div>
                        <form action="{{ route('admin.moderation.settings') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="box-body">
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="vpn_blocker_enabled" value="1" {{ $settings->vpn_blocker_enabled ? 'checked' : '' }}>
                                        Enable VPN Blocker
                                    </label>
                                    <p class="text-muted small">When enabled, VPN connections will be blocked from accessing the panel.</p>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="discord_webhook_enabled" value="1" {{ $settings->discord_webhook_enabled ? 'checked' : '' }}>
                                        Enable Discord Webhook
                                    </label>
                                    <p class="text-muted small">When enabled, moderation actions will be sent to Discord webhook.</p>
                                </div>
                                <div class="form-group">
                                    <label for="discord_webhook_url">Discord Webhook URL</label>
                                    <input type="url" class="form-control" id="discord_webhook_url" name="discord_webhook_url" value="{{ $settings->discord_webhook_url }}" placeholder="https://discord.com/api/webhooks/...">
                                    <p class="text-muted small">Enter your Discord webhook URL to receive moderation logs.</p>
                                </div>
                                <hr>
                                <h4>Login Security</h4>
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="block_on_ban" value="1" {{ $settings->block_on_ban ? 'checked' : '' }}>
                                        Block Banned Users from Logging In
                                    </label>
                                    <p class="text-muted small">When enabled, banned users cannot log in and will see the ban reason.</p>
                                </div>
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="track_login_history" value="1" {{ $settings->track_login_history ? 'checked' : '' }}>
                                        Track Login History
                                    </label>
                                    <p class="text-muted small">When enabled, all login attempts (successful and failed) will be logged.</p>
                                </div>
                                <div class="form-group">
                                    <label for="lockout_duration_minutes">Lockout Duration (Minutes)</label>
                                    <input type="number" class="form-control" id="lockout_duration_minutes" name="lockout_duration_minutes" value="{{ $settings->lockout_duration_minutes }}" min="1" max="60" required>
                                    <p class="text-muted small">How long to block login attempts after 5 failed attempts (1-60 minutes).</p>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <a href="{{ route('admin.moderation.login-history') }}" class="btn btn-info">
                                        <i class="fa fa-history"></i> View Login History
                                    </a>
                                    <a href="{{ route('admin.moderation.failed-attempts') }}" class="btn btn-warning">
                                        <i class="fa fa-exclamation-triangle"></i> View Failed Attempts
                                    </a>
                                </div>
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Save Settings</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
