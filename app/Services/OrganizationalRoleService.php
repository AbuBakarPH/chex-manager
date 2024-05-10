<?php

namespace App\Services;

use App\Models\Admin\Team;
use App\Services\GeneralService;
use App\Models\OrganizationalRole;
use App\Services\Interfaces\OrganizationalRoleInterface;

/**
 * Class OrganizationalRoleService
 * @package App\Services
 */
class OrganizationalRoleService implements OrganizationalRoleInterface
{
    public function __construct(private Team $model, private GeneralService $generalService, private TeamNotificationService $team_notificaiton)
    {
        $this->generalService = $generalService;
    }

    public function index($request)
    {
        $data = OrganizationalRole::query();
        $data = $this->generalService->handleSearch($request['searchText'], $data, ['name']);

        if ($request['perpage'] == 'All') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            return $this->generalService->handlePagination($request, $data);
        }
    }
}
