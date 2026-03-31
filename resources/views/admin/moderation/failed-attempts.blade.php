@extends('layouts.admin')

@section('title')
Failed Login Attempts
@endsection

@section('content-header')
<h1>
    Failed Login Attempts
    <small>View and manage failed login attempts.</small>
</h1>
<ol class="breadcrumb">
    <li><a href="{{ route('admin.index') }}">Admin</a></li>
    <li><a href="{{ route('admin.moderation') }}">Moderation</a></li>
    <li class="active">Failed Login Attempts</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Failed Login Attempts</h3>
                <div class="box-tools">
                    <form action="{{ route('admin.moderation.clear-failed-attempts') }}" method="POST" style="display: inline;">
                        @csrf
                        @method('POST')
                        <button type="submit" class="btn btn-sm btn-default" onclick="return confirm('Clear old failed attempts (older than 24 hours)?');">
                            <i class="fa fa-trash"></i> Clear Old
                        </button>
                    </form>
                </div>
            </div>
            <div class="box-body">
                <div class="alert alert-info">
                    <strong>Currently Blocked:</strong> {{ $blockedCount }} IP addresses/emails are currently blocked.
                </div>
                <div class="table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Email</th>
                                <th>IP Address</th>
                                <th>Attempted At</th>
                                <th>Status</th>
                                <th>Blocked Until</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attempts as $attempt)
                            <tr class="{{ $attempt->is_blocked && $attempt->blocked_until > now() ? 'danger' : '' }}">
                                <td>{{ $attempt->id }}</td>
                                <td>{{ $attempt->email ?? '-' }}</td>
                                <td>{{ $attempt->ip_address }}</td>
                                <td>{{ $attempt->attempted_at->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    @if($attempt->is_blocked && $attempt->blocked_until > now())
                                        <span class="label label-danger">Blocked</span>
                                    @elseif($attempt->is_blocked)
                                        <span class="label label-warning">Expired</span>
                                    @else
                                        <span class="label label-default">Active</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attempt->blocked_until)
                                        {{ $attempt->blocked_until->format('Y-m-d H:i:s') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($attempt->is_blocked && $attempt->blocked_until > now())
                                        <form action="{{ route('admin.moderation.unblock-failed-attempt', $attempt->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-xs btn-success">
                                                <i class="fa fa-unlock"></i> Unblock
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    <p class="text-muted">No failed login attempts found.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="box-footer">
                {{ $attempts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

