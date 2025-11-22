@extends('layouts.admin')

@section('title')
Login History
@endsection

@section('content-header')
<h1>
    Login History
    <small>View all login attempts.</small>
</h1>
<ol class="breadcrumb">
    <li><a href="{{ route('admin.index') }}">Admin</a></li>
    <li><a href="{{ route('admin.moderation') }}">Moderation</a></li>
    <li class="active">Login History</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Login History</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>IP Address</th>
                            <th>User Agent</th>
                            <th>Status</th>
                            <th>Failure Reason</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $entry)
                        <tr>
                            <td>{{ $entry->id }}</td>
                            <td>
                                @if($entry->user)
                                    {{ $entry->user->email }}
                                    <br><small class="text-muted">{{ $entry->user->username }}</small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ $entry->ip_address }}</td>
                            <td>{{ Str::limit($entry->user_agent, 50) }}</td>
                            <td>
                                @if($entry->successful)
                                    <span class="label label-success">Success</span>
                                @else
                                    <span class="label label-danger">Failed</span>
                                @endif
                            </td>
                            <td>{{ $entry->failure_reason ?? '-' }}</td>
                            <td>{{ $entry->logged_in_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                <p class="text-muted">No login history found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="box-footer">
                {{ $history->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

