@extends('layouts.admin')

@section('title', 'Laravel Logs')

@section('content-header')
    <h1>Laravel Logs<small>View system logs</small></h1>
@endsection

@section('content')
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">System Logs</h3>
            <!-- Dropdown to select log file -->
            <form action="{{ route('admin.laravel-logs.laravel') }}" method="GET" class="pull-right">
                <label for="log_file">Select Log File:</label>
                <select name="log_file" class="log_file" id="log_file" onchange="this.form.submit()">
                    @foreach ($logFiles as $file)
                        <option value="{{ $file }}" {{ $selectedLogFile == $file ? 'selected' : '' }}>
                            {{ basename($file) }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="box-body">
            <!-- Display logs -->
            <pre style="white-space: pre-wrap; word-wrap: break-word; max-height: 600px; overflow-y: scroll;">
                {{ $logs }}
            </pre>
        </div>
        <div class="box-footer">
        <!-- Download Logs Button -->
            <a href="{{ route('admin.laravel-logs.download', ['log_file' => $selectedLogFile]) }}" class="btn btn-primary" style="margin-left: 10px;">
            <i class="fa-solid fa-file-arrow-down"></i> Download Log File
            </a>
        </div>
    </div>
@endsection
