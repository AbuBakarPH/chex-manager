<?php

namespace App\Http\Controllers\ManagerControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Interfaces\RoleInterface;
use App\Http\Requests\StoreRoleRequest;
use Spatie\Permission\Models\Permission as Permission;
use Spatie\Permission\Models\Role as Role;


class RoleController extends Controller
{

    public function __construct(private RoleInterface $service)
    {
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {   
        return $this->response('Role listing', $this->service->index($request), 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        return $this->response(
            'Role created successfully',
            $this->service->store($request),
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data['role'] = Role::where('id', $id)->first();
        $data['role_permissions'] = $data['role']->getPermissionNames();
        $permissions = Permission::get();
    
        foreach ($permissions as $key => $permission) {
            $module = explode('-', $permission->name);
            $chunks[$module[0]][] =  $permission->name;
        }
        $data['permissions'][]= $chunks ;
        return $this->response('Role detail', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRoleRequest $request, string $id)
    {
        $role = Role::findOrFail($id);
        return $this->response(
            'Role updated successfully',
            $this->service->update($request, $role)
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
