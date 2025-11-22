<?php

namespace Pterodactyl\Http\Requests\Admin\AktiCubeDevelopmentTeam;

use Pterodactyl\Http\Requests\Admin\AdminFormRequest;

class StorePermissionManagerRoleRequest extends AdminFormRequest
{
    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|regex:/^#[a-f0-9]{6}$/i',
            'permissions' => 'nullable|array',
        ];
    }
}
