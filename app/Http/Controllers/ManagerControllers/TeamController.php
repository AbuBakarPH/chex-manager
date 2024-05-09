<?php

namespace App\Http\Controllers\ManagerControllers;

use App\Models\User;
use App\Models\Admin\Team;
use Illuminate\Http\Request;
use App\Services\GeneralService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeamRequest;
use App\Services\Interfaces\TeamInterface;



class TeamController extends Controller
{
    public function __construct(private TeamInterface $service, private GeneralService $generalService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->response('Team listing', $this->service->index($request), 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTeamRequest $request)
    {
        // if($this->generalService->hasLimit('teams')) {
        return $this->response(
            'Team created successfully',
            $this->service->store($request),
            200
        );
        // }else{
        //     return $this->response('Your Limit Exceeded To Create Team', NULL, 403);
        // }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $team = Team::whereId($id)->first();
        $data['team'] = Team::with('company')->whereId($id)->first();
        $teamUserId = $team->users->pluck('id')->toArray();
        $data['team']['user_id'] = $teamUserId;
        $data['users'] = User::whereIn('id', $teamUserId)->orwhere(function ($query) {
            $query->where('status', 1)->where('company_id', auth()->user()->company_id);
        })->role('staff')->get();
        return $this->response('Team detail', $data);
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
    public function update(StoreTeamRequest $request, string $id)
    {
        $team = Team::whereId($id)->first();
        return $this->response(
            'Team updated successfully',
            $this->service->update($request, $team),
            200
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
