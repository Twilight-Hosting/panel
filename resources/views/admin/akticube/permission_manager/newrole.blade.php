@extends('layouts.admin')

@section('title')
    Creating a new role
@endsection

@section('content-header')
    <h1>Roles<small>Create a new role.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.akticube.permission-manager') }}">Roles</a></li>
        <li class="active">New Role</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <form method="post">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Characteristics</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label for="name" class="control-label">Name <span class="field-required"></span></label>
                            <div>
                                <input type="text" autocomplete="off" name="name" class="form-control" value="{{ old('name') }}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description" class="control-label">Description <span class="field-optional"></span></label>
                            <textarea name="description" id="pDescription" rows="4" class="form-control">{{ old('description') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="color" class="control-label">Color <span class="field-required"></span></label>
                            <div>
                                <input type="color" name="color" class="form-control" value="{{ old('color') ?? '#0084FF' }}"/>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        {!! csrf_field() !!}
                        <input type="submit" value="Create role" class="btn btn-success btn-sm">
                    </div>
                </div>
            </div>
        </form>
        <div class="col-md-6">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Informations</h3>
                </div>
                <div class="box-body">
                    <div class="form-group col-md-12">
                        <h4 class="text-red">IMPORTANT !</h4>
                        <p>If you need help for anything, please join our <a href="https://discord.gg/RJ2A8yYS2m" target="_blank">Discord</a>.</p>
                        <p>Don't forget to read the <a href="https://git.ric-rac.org/ric-rac/addons-documentation/wiki/Installing-Permission-Manager-for-Pterodactyl" target="_blank">documentation</a> !</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
