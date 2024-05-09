<?php

namespace App\Services;

use App\Services\Interfaces\PermissionInterface;
use Spatie\Permission\Models\Permission;

/**
 * Class RoleService
 * @package App\Services
 */
class PermissionService extends GeneralService implements PermissionInterface  
{
    public function __construct(private Permission $model)
    {
        parent::__construct($model);
    } 
    
    public function permissionList($model)
    {
        $permissions = $this->model->get();
        $chunks = [];
        foreach ($permissions as $key => $permission) {
            $module = explode('-', $permission->name);
            $chunks[$module[0]][] = $permission->name;
        }
        
        return $chunks ;
    }

}