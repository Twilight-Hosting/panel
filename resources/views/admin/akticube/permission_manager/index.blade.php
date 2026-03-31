@extends('layouts.admin')

@section('title')
    Roles
@endsection

@section('content-header')
    <h1>Roles<small>Configure and manage authorization roles.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Roles</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">List of roles</h3>
                    <div class="box-tools search01">
                        <form action="" method="GET">
                            <div class="input-group input-group-sm">
                                <input type="text" name="filter[name]" class="form-control pull-right" value="{{ request()->input('filter.name') }}" placeholder="Search a role">
                                <div class="input-group-btn">
                                    <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                                    <a href="{{ route('admin.akticube.permission-manager.roles.new') }}"><button type="button" class="btn btn-sm btn-primary" style="border-radius: 0 3px 3px 0;margin-left:-1px;">Create a new role</button></a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <tbody>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Number of Permissions</th>
                                <th>Number of Users having this role</th>
                                <th></th>
                            </tr>
                            @foreach($roles as $role)
                                <tr>
                                    <td>{{ $role->id }}</td>
                                    <td><a href="{{ route('admin.akticube.permission-manager.roles.view', $role->id) }}"><span style="background-color: {{ $role->color }}; color: {{ $role->getComplementaryColorAttribute() }}" class="label ">{{ $role->name }}</span></a></td>
                                    <td>{{ $role->getNumberOfPermissions() }}</td>
                                    <td>{{ $role->getNumberOfUsersHavingThisRole() }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.akticube.permission-manager.roles.view', $role->id) }}"><button type="button" class="btn btn-xs btn-primary"><i class="fa fa-wrench"></i></button></a>
                                        <button data-action="delete" data-id="{{ $role->id }}" type="button" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($roles->hasPages())
                    <div class="box-footer with-border">
                        <div class="col-md-12 text-center">{!! $roles->appends(['query' => Request::input('query')])->render() !!}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('footer-scripts')
    @parent
    <script>
        $('[data-action="delete"]').click(function (event) {
            event.preventDefault();
            const self = $(this);
            swal({
                title: 'Are you sure you wanna delete this role ?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d9534f',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Delete',
                showLoaderOnConfirm: true,
            }, function () {
                $.ajax({
                    method: 'DELETE',
                    url: '/admin/permission-manager/delete/' + self.data('id'),
                    data: {
                        _token: '{{ csrf_token() }}'
                    }, complete: function () {
                        window.location.href = '/admin/permission-manager';
                    }
                });
            });
        });
    </script>
@endsection
