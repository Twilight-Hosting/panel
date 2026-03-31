@extends('layouts.admin')

@section('title')
IP Ban
@endsection

@section('content-header')
<h1>
    IP Ban
    <small>Ban an IP address from accessing the panel.</small>
</h1>
<ol class="breadcrumb">
    <li><a href="{{ route('admin.index') }}">Admin</a></li>
    <li><a href="{{ route('admin.moderation') }}">Moderation</a></li>
    <li class="active">IP Ban</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">IP Ban Details</h3>
            </div>
            <form action="{{ route('admin.moderation.ip-ban') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label for="ip_address">IP Address</label>
                        <input type="text" class="form-control" id="ip_address" name="ip_address" value="{{ old('ip_address') }}" placeholder="192.168.1.1" required>
                        <p class="text-muted small">Enter the IP address to ban.</p>
                    </div>

                    <div class="form-group">
                        <label for="reason">Ban Reason</label>
                        <textarea class="form-control" id="reason" name="reason" rows="4" required placeholder="Enter the reason for banning this IP address...">{{ old('reason') }}</textarea>
                        <p class="text-muted small">Provide a clear reason for the IP ban.</p>
                    </div>

                    <div class="form-group">
                        <label for="expires_at">Expiration Date (Optional)</label>
                        <input type="datetime-local" class="form-control" id="expires_at" name="expires_at" value="{{ old('expires_at') }}">
                        <p class="text-muted small">Leave empty for permanent ban. Otherwise, the ban will automatically expire at the specified date.</p>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-warning">Ban IP Address</button>
                    <a href="{{ route('admin.moderation') }}" class="btn btn-default">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

