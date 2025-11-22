@extends('layouts.admin')

@section('title')
Ban User
@endsection

@section('content-header')
<h1>
    Ban User
    <small>Ban a user from the panel.</small>
</h1>
<ol class="breadcrumb">
    <li><a href="{{ route('admin.index') }}">Admin</a></li>
    <li><a href="{{ route('admin.moderation') }}">Moderation</a></li>
    <li class="active">Ban User</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Ban Details</h3>
            </div>
            <form action="{{ route('admin.moderation.store') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label for="user_id">User</label>
                        <select class="form-control" id="user_id" name="user_id" required>
                            <option value="">Select a user...</option>
                        </select>
                        <input type="text" class="form-control" id="user_search" placeholder="Search users..." style="margin-top: 10px;">
                        <p class="text-muted small">Start typing to search for users (minimum 2 characters).</p>
                    </div>

                    <div class="form-group">
                        <label for="reason">Ban Reason</label>
                        <textarea class="form-control" id="reason" name="reason" rows="4" required placeholder="Enter the reason for banning this user...">{{ old('reason') }}</textarea>
                        <p class="text-muted small">Provide a clear reason for the ban.</p>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="ip_ban" value="1" {{ old('ip_ban') ? 'checked' : '' }}>
                            IP Ban
                        </label>
                        <p class="text-muted small">When enabled, the user's IP address will also be banned.</p>
                    </div>

                    <div class="form-group">
                        <label for="expires_at">Expiration Date (Optional)</label>
                        <input type="datetime-local" class="form-control" id="expires_at" name="expires_at" value="{{ old('expires_at') }}">
                        <p class="text-muted small">Leave empty for permanent ban. Otherwise, the ban will automatically expire at the specified date.</p>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-danger">Ban User</button>
                    <a href="{{ route('admin.moderation') }}" class="btn btn-default">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userSearch = document.getElementById('user_search');
    const userIdSelect = document.getElementById('user_id');
    let searchTimeout;

    userSearch.addEventListener('input', function() {
        const query = this.value.trim();
        
        if (query.length < 2) {
            userIdSelect.innerHTML = '<option value="">Select a user...</option>';
            return;
        }

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            fetch('{{ route("admin.moderation.users.search") }}?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    userIdSelect.innerHTML = '<option value="">Select a user...</option>';
                    data.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = user.email + ' (' + user.username + ')';
                        userIdSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error:', error));
        }, 300);
    });
});
</script>
@endsection

