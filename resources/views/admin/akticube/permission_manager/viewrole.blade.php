@extends('layouts.admin')

@section('title')
    Editing {{ $role->name }}
@endsection

@section('content-header')
    <h1>Roles<small>Editing the {{ $role->name }} role.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.akticube.permission-manager') }}">Roles</a></li>
        <li class="active">{{ $role->name }}</li>
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
                                <input type="text" autocomplete="off" name="name" class="form-control" value="{{ $role->name }}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description" class="control-label">Description <span class="field-optional"></span></label>
                            <textarea name="description" id="pDescription" rows="4" class="form-control">{{ $role->description }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="color" class="control-label">Color <span class="field-required"></span></label>
                            <div>
                                <input type="color" name="color" class="form-control" value="{{ $role->color}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        {!! csrf_field() !!}
                        {!! method_field('PATCH') !!}
                        <input type="submit" value="Update role" class="btn btn-success btn-sm">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Informations</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group col-md-12">
                            <h4 class="text-red">IMPORTANT!</h4>
                            <p>If you need help for anything, please join our <a href="https://discord.gg/RJ2A8yYS2m" target="_blank">Discord</a>.</p>
                            <p>Don't forget to read the <a href="https://git.ric-rac.org/ric-rac/addons-documentation/wiki/Installing-Permission-Manager-for-Pterodactyl" target="_blank">documentation</a>!</p>
                            <p class="text-red">Don't leave behind the fact that if someone has the permission to access this part of the addon through a role itself, that same person has the capacity to edit its own permissions.</p>
                            <p>If you want someone to have access to the button to get to the admin area on the client side, just add to the role the Allowed Admin Routes <code>GET /admin</code>. <button id="pButtonSelectAdminIndexRoute" type="button" class="btn btn-xs btn-primary">Select this route</button></p>
                            <p>Same thing if you want someone to have access to the part where you can see the others servers on the panel on the client side, just add to the role the Client Permission <code>websocket.connect</code>. <button id="pButtonSelectWebsocketConnect" type="button" class="btn btn-xs btn-primary">Select this route</button></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Client Permissions</h3> <button id="pButtonSelectAllClientPermissions" type="button" class="btn btn-xs btn-primary">Select all permissions</button> <button id="pButtonUnselectAllClientPermissions" type="button" class="btn btn-xs btn-danger">Unselect all permissions</button>
                    </div>
                    <div class="box-body">
                        <p>The members of this role will gain these permissions (in addition to eventual subuser permissions) on all servers except the excluded servers below.</p>
                        <div class="form-group">
                            <select class="form-control" id="pClientPermissions" name="permissions[]" multiple>
                                @foreach ($clientPermissions as $groupName => $clientPermission)
                                    <optgroup label="{{ ucfirst($groupName) }} : {{ $clientPermission['description'] }}">
                                        @foreach ($clientPermission['keys'] as $keyName => $permissionDescription)
                                            <option id="pPermission" value="{{ $groupName }}.{{ $keyName }}" {{ in_array($groupName . "." . $keyName, $role->permissions ?? []) ? 'selected' : '' }}>{{ $groupName}}.{{ $keyName }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <h4>Excluded servers</h4>
                        <p>The members of this role won't receive the additional permissions for the following servers.</p>
                        @foreach ($excludedServers as $excludedServer)
                            <div class="form-group">
                                <input class="form-control" type="number" placeholder="Server numeric identifier"
                                        name="excluded_servers[]" value="{{ $excludedServer }}">
                            </div>
                        @endforeach

                        <div class="form-group">
                            <input class="form-control" type="number" placeholder="Server numeric identifier"
                                    name="excluded_servers[]">
                        </div>
                        <button class="btn btn-sm btn-success" type="button" id="addExcludedInput">Add</button>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Allowed Admin routes</h3>
                        <button id="pButtonSelectAllRoutes"
                                type="button"
                                class="btn btn-xs btn-primary">
                                Select all routes</button>
                        <button id="pButtonUnselectAllRoutes"
                                type="button"
                                class="btn btn-xs btn-danger">
                                Unselect all routes
                        </button>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <select class="form-control" id="pAdminRoutes" name="admin_routes[]" multiple>
                                @foreach ($routes as $route)
                                    @foreach ($route->methods() as $method)
                                        @if ($method !== "HEAD")
                                            <option value="{{ str_replace('/', '.', $route->uri) }}|{{ $method }}" @if ($role->isRouteAllowed($route)) selected @endif>{{ $method }} /{{ $route->uri }}</option>
                                        @endif
                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="col-md-12">
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title">Delete Role</h3>
                </div>
                <div class="box-body">
                    <p class="no-margin">There must be no users associated with this role in order for it to be deleted.</p>
                </div>
                <div class="box-footer">
                    <form action="{{ route('admin.akticube.permission-manager.roles.delete', $role->id) }}" method="POST">
                        {!! csrf_field() !!}
                        {!! method_field('DELETE') !!}
                        <input id="delete" type="submit" class="btn btn-sm btn-danger pull-right" @if ($role->getNumberOfUsersHavingThisRole() > 0) disabled @endif value="Delete Role" />
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer-scripts')
    @parent
    <script>
        $('#pButtonSelectAdminIndexRoute').click(function () {
            $('#pAdminRoutes > option').each(function () {
                if ($(this).val() === 'admin|GET') {
                    $(this).prop('selected', true);
                }
            });
            $('#pAdminRoutes').trigger('change');
        });

        document.getElementById('addExcludedInput').addEventListener('click', function (event) {
            const newElement = event.target.previousElementSibling.cloneNode(true);
            newElement.children[0].value = '';
            event.target.insertAdjacentElement('beforebegin', newElement);
        })

        $('#pButtonSelectWebsocketConnect').click(function () {
            $('#pClientPermissions option').each(function () {
                if ($(this).val() === 'websocket.connect') {
                    $(this).prop('selected', true);
                }
            });
            $('#pClientPermissions').trigger('change');
        });

        $('#pClientPermissions').select2({
            tags: true,
            selectOnClose: false,
            tokenSeparators: [',', ' '],
        });

        $('#pAdminRoutes').select2({
            tags: true,
            selectOnClose: false,
            tokenSeparators: [',', ' '],
        });

        $('#pButtonSelectAllClientPermissions').click(function () {
            $('#pClientPermissions optgroup option').each(function () {
                $(this).prop('selected', true);
            });
            $('#pClientPermissions').trigger('change');
        });

        $('#pButtonUnselectAllClientPermissions').click(function () {
            $('#pClientPermissions optgroup option').each(function () {
                $(this).prop('selected', false);
            });
            $('#pClientPermissions').trigger('change');
        });

        $('#pButtonSelectAllRoutes').click(function () {
            $('#pAdminRoutes > option').prop('selected', true);
            $('#pAdminRoutes').trigger('change');
        });

        $('#pButtonUnselectAllRoutes').click(function () {
            $('#pAdminRoutes > option').prop('selected', false);
            $('#pAdminRoutes').trigger('change');
        });
    </script>
@endsection
