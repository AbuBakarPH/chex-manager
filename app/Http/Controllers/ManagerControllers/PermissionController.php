<?php

namespace App\Http\Controllers\ManagerControllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Services\Interfaces\PermissionInterface;


class PermissionController extends Controller 
{
    
   
    public function __construct(private PermissionInterface $service)
    {
    } 
    
    public function index(Request $request)
    {
        return $this->response('Permission listing', $this->service->permissionList($request, null, [], false), 200);
    }
    
}
