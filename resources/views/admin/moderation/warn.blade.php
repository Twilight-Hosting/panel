@extends('layouts.admin')

@section('title')
Warn User
@endsection

@section('content-header')
<h1>
    Warn User
    <small>Issue a warning to a user.</small>
</h1>
<ol class="breadcrumb">
    <li><a href="{{ route('admin.index') }}">Admin</a></li>
    <li><a href="{{ route('admin.moderation') }}">Moderation</a></li>
    <li class="active">Warn User</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Warning Details</h3>
            </div>
            <form action="{{ route('admin.moderation.warn') }}" method="POST">
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
                        <label for="reason">Warning Reason</label>
                        <textarea class="form-control" id="reason" name="reason" rows="4" required placeholder="Enter the reason for warning this user...">{{ old('reason') }}</textarea>
                        <p class="text-muted small">Provide a clear reason for the warning.</p>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-warning">Warn User</button>
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

