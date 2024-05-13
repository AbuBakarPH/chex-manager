<?php

namespace App\Services;

use App\Services\Interfaces\TeamInterface;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GeneralNotification;
use App\Models\Admin\TeamUser;
use App\Models\Admin\Team;
use App\Services\GeneralService;
use App\Jobs\SendEmailJob;
use Illuminate\Support\Facades\Auth;
use App\Events\SendNotification;
use App\Models\User;


/**
 * Class UserService
 * @package App\Services
 */
class TeamService implements TeamInterface
{
    public function __construct(private Team $model, private GeneralService $generalService, private TeamNotificationService $team_notificaiton)
    {
        $this->generalService = $generalService;
    }

    public function index($request)
    {
        $where =  [
            ['key' => 'company_id', 'operator' => '=', 'value' => auth()->user()->company_id],
        ];
        if ($request['status']) {
            $status = ['key' => 'is_active', 'operator' => '=', 'value' =>  $request['status']];
            array_push($where, $status);
        }

        $data = Team::with(['users', 'users.roles', 'users.photo', 'role', 'category']);
        $data = $this->generalService->handleWhere($data, $where);
        $data = $this->generalService->handleSearch($request['searchText'], $data, ['title'], 'is_active');

        if ($request['perpage'] == 'All') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            return $this->generalService->handlePagination($request, $data);
        }
    }

    public function store($request)
    {
        $validated = $request->validated();
        $validated['users_id'] = Auth::id();
        $validated['company_id'] = Auth::user()->company_id;
        $team = Team::create($validated);
        $team->users()->attach($validated['user_id']);
        $this->team_notificaiton->forCreatingTeam($team);
        return $team;
    }

    public function update($request, $model)
    {
        $validated = $request->validated();
        $validated['company_id'] = Auth::user()->company_id;
        $this->team_notificaiton->forUpdatingTeam($model, $validated);
        $model->update($validated);
        $model->users()->sync($validated['user_id']);
        return $model;
    }
}
