<?php

namespace Pterodactyl\Http\Controllers\Admin\AktiCubeDevelopmentTeam;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Http\Requests\Admin\AktiCubeDevelopmentTeam\StorePermissionManagerRoleRequest;
use Pterodactyl\Http\Requests\Admin\AktiCubeDevelopmentTeam\UpdatePermissionManagerRoleRequest;
use Pterodactyl\Models\Permission;
use Pterodactyl\Models\PermissionRole;
use Pterodactyl\Services\AktiCubeDevelopmentTeam\PermissionManagerRoleCreationService;
use Spatie\QueryBuilder\QueryBuilder;

class PermissionManagerController extends Controller
{
    protected $alert;

    protected $creationService;

    public function __construct(
        AlertsMessageBag $alert,
        PermissionManagerRoleCreationService $creationService,
    ) {
        $this->alert = $alert;
        $this->creationService = $creationService;
    }

    public function index()
    {
        $roles = QueryBuilder::for(PermissionRole::query())->allowedFilters(['name'])->paginate(10);
        return view('admin.akticube.permission_manager.index', [
            'roles' => $roles,
        ]);
    }

    public function createRole()
    {
        return view('admin.akticube.permission_manager.newrole');
    }

    public function storeRole(StorePermissionManagerRoleRequest $request): RedirectResponse
    {
        try {
            $data = $request->normalize();
            $permission_role = $this->creationService->handle($data);
            $this->alert->success('Permissions role successfully created.')->flash();

            return redirect()->route('admin.akticube.permission-manager.roles.view', $permission_role->id);
        } catch (\Exception $exception) {
            $this->alert->danger($exception->getMessage())->flash();
            return redirect()->route('admin.akticube.permission-manager');
        }
    }

    public function viewRole(int $role_id)
    {
        $role = PermissionRole::query()->findOrFail($role_id);

        $clientPermissions = Permission::permissions()->toArray();

        // Add specific permissions for the websocket that aren't in the Permission model
        $clientPermissions['websocket']['keys']['errors'] = "Allows a user to see the websocket errors in the console.";
        $clientPermissions['websocket']['keys']['install'] = "Allows a user to see the installation process of the server in the console.";
        $clientPermissions['websocket']['keys']['transfer'] = "Allows a user to see the status of the transfer of the server to an another node.";

        $routes = Route::getRoutes()->getRoutes();
        $routeCount = count($routes);
        $adminRoutes = [];

        // Get only the admin routes
        for($i = 0; $i < $routeCount; $i++) {
            $route = $routes[$i];

            if(str_contains($route->uri, 'admin')) {
                $adminRoutes[] = $route;
            }
        }

        return view('admin.akticube.permission_manager.viewrole', [
            'role' => $role,
            'clientPermissions' => $clientPermissions,
            'excludedServers' => $role->excluded_servers,
            'routes' => $adminRoutes
        ]);
    }

    public function updateRole(UpdatePermissionManagerRoleRequest $request, int $role_id)
    {
        $role = PermissionRole::query()->findOrFail($role_id);

        try {
            $data = $request->normalize([
                'name', 'description', 'color', 'permissions', 'admin_routes',
                'excluded_servers',
            ]);

            if (!isset($data['permissions'])) {
                $data['permissions'] = [];
            }

            if (!isset($data['admin_routes'])) {
                $data['admin_routes'] = [];
            } else {
                if (in_array('admin|GET', $data['admin_routes'])) {
                    $data['admin_routes'][] = 'admin.index|GET';
                }
            }

            $data['excluded_servers'] = array_values(array_unique(array_filter($data['excluded_servers'])));

            $role->update($data);
            $this->alert->success('Role ' . $role->name . ' successfully updated.')->flash();

            return redirect()->route('admin.akticube.permission-manager.roles.view', $role->id);
        } catch (\Exception $exception) {
            $this->alert->danger($exception->getMessage())->flash();
            return redirect()->route('admin.akticube.permission-manager');
        }
    }

    public function deleteRole(int $role_id)
    {
        $role = PermissionRole::query()->findOrFail($role_id);

        if($role->getNumberOfUsersHavingThisRole() > 0) {
            $this->alert->danger('Role ' . $role->name . ' can\'t be deleted cause some users still have it.')->flash();
            return redirect()->route('admin.akticube.permission-manager.roles.view', $role->id);
        } else {
            $role->delete();
            $this->alert->success('Role ' . $role->name . ' successfully deleted.')->flash();
            return redirect()->route('admin.akticube.permission-manager');
        }
    }
}
