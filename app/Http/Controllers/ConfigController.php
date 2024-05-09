<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;
use App\Services\ConfigService;
use App\Services\GeneralService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConfigRequest;
use App\Http\Requests\UpdateConfigRequest;
use App\Models\ConfigAssignee;

class ConfigController extends Controller
{
    public function __construct(private ConfigService $service, private GeneralService $generalService)
    {
    }

    public function index(Request $request)
    {
        return $this->response('Config listing', $this->service->index($request), 200);
    }

    public function store(StoreConfigRequest $request)
    {
        $data = $request->validated();
        return $this->response(
            'Config created successfully',
            $this->service->store($data),
        );
    }

    public function show($id)
    {
        $my_checklist = Config::whereId($id)->with([
            'schedules.answers.user',
            'schedules.answers.photo',
            'staff.photo',
            'task.category',
            'approvers.photo',
            'task.sections.questions.fields',
            'task.sections.questions.risk.assignees',
            'task.sections.questions.completed_risks',
            'task.sections.questions.sub_questions.fields',
            'task.sections.questions.risk.reason.user.photo',
            'task.sections.questions.risk.conversations.user.photo',
        ])->first();

        return $this->response('checklist detail', $my_checklist, 200);
    }

    public function update(UpdateConfigRequest $request, $id)
    {
        $data = $request->validated();
        $this->service->update($data, $id);
        return response()->noContent();
    }

    public function updateChecklistStatus(Request $request, $id)
    {
        $my_checklist = Config::findOrFail($id);
        $my_checklist->update(['is_active' => !$my_checklist->is_active]);
        $updated_status = $my_checklist->is_active ? 'active' : 'Inactive';
        return $this->response('checklist status ' . $updated_status, $my_checklist, 200);
    }

    public function getDailyChecklistByConfig($id)
    {
        // $config = Config::with([
        //     'daily_checklists.checklist.sections.questions.question_answers' => function ($q) {
        //         $q->where('daily_checklist_id', 'daily_checklists.id');
        //         // ->where('checklist_id', '=', 'checklist.id');
        //     }
        // ])
        //     ->whereHas('checklist.sections', function ($q) {
        //         $q->where('status', 'active');
        //     })
        //     ->whereHas('checklist.sections.questions', function ($q) {
        //         $q->where('status', 'active');
        //     })
        //     // ->with('daily_checklists.checklist.sections.questions.question_answers', function ($q) {
        //     //     $q->where('daily_checklist_id', 119);
        //     //         // ->where('checklist_id', '=', 'checklist.id');
        //     // })
        //     ->where('id', $id)->first();

        $config = Config::with([
            'daily_checklists.checklist.sections.questions.question_answers' => function ($q) {
                $q->whereColumn('daily_checklist_answers.daily_checklist_id', 'daily_checklists.id');
            }
        ])
            ->whereHas('checklist.sections', function ($q) {
                $q->where('status', 'active');
            })
            ->whereHas('checklist.sections.questions', function ($q) {
                $q->where('status', 'active');
            })
            ->where('id', $id)
            ->first();


        return $this->response('Checklist config detail', $config, 200);
    }

    public function removeAssignee(Request $request)
    {
        $assignee = $request['assignee'];
        $config_id = $request['config_id'];
        $assige = ConfigAssignee::where('config_id', $config_id)->where('user_id', $assignee)->delete();
        return $this->response('Checklist config detail', $assige, 200);
    }

    public function checkRefainTask(Request $request, $id)
    {
        return $this->response('Config listing', $this->service->refainChecklist($id), 200);
    }

    public function updateConfigStatus($id)
    {
        $config = Config::where('id', $id)->first();
        $config->update(['is_active' => !$config->is_active]);
        return $this->response('config status updated', $config, 200);
    }
}
