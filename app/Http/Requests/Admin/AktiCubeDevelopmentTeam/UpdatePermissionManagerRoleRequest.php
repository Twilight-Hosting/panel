<?php

namespace Pterodactyl\Http\Requests\Admin\AktiCubeDevelopmentTeam;

use Illuminate\Database\Eloquent\Collection;
use Pterodactyl\Http\Requests\Admin\AdminFormRequest;
use Pterodactyl\Models\PermissionRole;

class UpdatePermissionManagerRoleRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|regex:/^#[a-f0-9]{6}$/i',
            'permissions' => 'nullable|array',
            'admin_routes' => 'nullable|array',
            'excluded_servers' => 'nullable|array',
            'excluded_servers.*' => 'nullable|integer|exists:Pterodactyl\Models\Server,id',
        ];
    }
}
