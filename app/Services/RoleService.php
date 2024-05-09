<?php

namespace App\Services;

use App\Services\Interfaces\RoleInterface;
use App\Models\Admin\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Auth;

/**
 * Class RoleService
 * @package App\Services
 */
class RoleService implements RoleInterface
{
    public function __construct(private Role $model, private GeneralService $generalService)
    {
        // parent::__construct($model);
    }

    public function index($request)
    {
        $data = Role::query();

        if (Auth::user()->roles->pluck('name')[0]  == 'Manager') {
            $data = $data->whereNotIn('name', ['Super Admin', 'Supplier']);
        }

        $data = $this->generalService->handleSearch($request['searchText'], $data, ['name']);

        if ($request['perpage'] == 'All') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            return $this->generalService->handlePagination($request, $data);
        }
    }

    public function store($request)
    {
        if (Auth::user()->roles->pluck('name')[0] !== 'Super Admin') {
            abort(422, 'Unauthorized: Permission Not Allow');
        }
        $validated = $request->validated();
        $validated['guard_name'] = 'web';
        $role = Role::create($validated);
        $role->givePermissionTo($validated['permissions']);
        // Notification::send(auth()->user(), new GeneralNotification($role, 'Role', 'POST', "Role Successfully created"));
        return $role;
    }

    public function update($request, $model)
    {
        if (Auth::user()->roles->pluck('name')[0] !== 'Super Admin') {
            abort(422, 'Unauthorized: Permission Not Allow');
        }
        $validated = $request->validated();
        $model->update($validated);
        $model->syncPermissions($validated['permissions']);
        // Notification::send(auth()->user(), new GeneralNotification($model, 'Role', 'Update', "Role Successfully Updated"));
        return $model;
    }
}
