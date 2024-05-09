<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuestionRiskRequest;
use App\Http\Requests\UpdateQuestionRiskRequest;
use App\Models\QuestionRisk;
use App\Models\RiskAssignee;
use App\Models\User;
use App\Services\GeneralService;
use App\Services\QuestionRiskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GeneralNotification;

class QuestionRiskController extends Controller
{
    public function __construct(private QuestionRiskService $service, private GeneralService $generalService)
    {
    }

    public function createRisk(QuestionRiskRequest $request)
    {
        $authRole = auth()->user()->roles->pluck('name')[0];
        if ($authRole != 'Manager') {
            abort(403, 'Unauthorized: Only Manager users are allowed.');
        }

        // if ($this->generalService->hasLimit('risk')) {
        $data = $request->validated();
        $risk = $this->service->createRisk($data);
        // $users = User::whereIn('id', $data['assignees'])->get();
        // $user2 = "";
        // if (isset($users[1])) {
        //     $user2  = $users[1]->name . ' ,';
        // }
        // Notification::send($users, new GeneralNotification($risk, 'Risk', 'POST', "Risk Assignment Alert: " . $users[0]->name  . ", " . $user2 . "and more have been assigned a risk. Check your email for details. ", 'Risk Assign', "Risk Assign"));
        // Notification::send(auth()->user(), new GeneralNotification($risk, 'Risk', 'POST', $users[0]->name  . ", " . $user2 . "and more have been assigned a risk. check your email for details and coordinate with the team for swift action.", 'Risk Assign', "Risk Assign"));

        return $this->response(
            'Risk created successfully',
            $risk->load(['assignees.photo', 'conversations.user.photo', 'reason.user.photo']),
            200
        );
        // } else {
        //     return $this->response('Your Limit Exceeded To Create Checklist', NULL, 403);
        // }
    }

    public function updateRisk(UpdateQuestionRiskRequest $request, $id)
    {
        $authRole = auth()->user()->roles->pluck('name')[0];
        if ($authRole != 'Manager') {
            abort(403, 'Unauthorized: Only Manager users are allowed.');
        }

        $data = $request->validated();
        $risk = $this->service->updateRisk($data, $id);
        return $this->response(
            'Risk updated successfully',
            $risk->load(['assignees', 'conversations.user.photo', 'reason.user.photo']),
            200
        );
    }

    public function index(Request $request)
    {
        return $this->response('Risks fetched successfully', $this->service->index($request), 200);
    }

    public function show($id)
    {
        $data = QuestionRisk::where('id', $id)->with(['question.section.task.category', 'question.section.task.mythBusters', 'assignees.photo', 'conversations.user.photo', 'conversations.photo'])->first();
        return $this->response('Risk detail', $data, 200);
    }

    public function listRequests(Request $request)
    {
        $authRole = auth()->user()->roles->pluck('name')[0];
        if ($authRole != 'Manager') {
            abort(403, 'Unauthorized: Only Manager users are allowed.');
        }

        $data = QuestionRisk::with('question')->where('status', 'draft');

        if ($request['perpage'] == 'all') {
            $data = $this->generalService->handleAllData($request, $data);
        } else {
            $data = $this->generalService->handlePagination($request, $data);
        }

        return $this->response(
            'Risks fetched successfully',
            $data,
            200
        );
    }

    public function csv_data(Request $request)
    {
        $data = QuestionRisk::with(['question', 'assignees', 'conversations.user'])->where('company_id', auth()->user()->company_id)->get();
        return $this->response('Risks fetched successfully', $data, 200);
    }
}
