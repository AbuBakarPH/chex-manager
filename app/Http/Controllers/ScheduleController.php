<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Services\Interfaces\ScheduleInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    public function __construct(private ScheduleInterface $service)
    {
        //
    }

    public function index(Request $request)
    {
        return $this->response('Schedule listing', $this->service->index($request), 200);
    }

    public function show($id)
    {
        $schedule = Schedule::with([
            'answers.user',
            'answers.photo',
            'task.category',
            'configuration.assignees.photo',
            'task.sections.questions.fields',
            'task.sections.questions.risk.assignees',
            'task.sections.questions.completed_risks',
            'task.sections.questions.sub_questions.fields',
            'task.sections.questions.risk.reason.user.photo',
            'task.sections.questions.risk.conversations.user.photo',
        ])->where('id', $id)->first();
        return $this->response('Schedule detail', $schedule, 200);
    }

    public function approversSchedules(Request $request)
    {
        return $this->response('Schedule listing', $this->service->approversScheduleList($request), 200);
    }
}
