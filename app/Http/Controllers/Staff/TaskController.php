<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\FetchHistorySchedule;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Config;
use Carbon\Carbon;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $scheduleTask = Schedule::with([
            'answers',
            'task.category',
            'configuration.assignees.photo',
            'task.sections.questions.risk',
            'task.sections.questions.fields',
            'task.sections.questions.sub_questions.fields',
            'task.sections.questions.sub_questions.answer',
        ])->where('id', $id)->first();
        return $this->response('Schedule task', $scheduleTask, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function schduleTasks(Request $request)
    {
        $scheduleTasks = $this->taskList($request, ['due', 'in_progress']);
        return $this->response('Schedule detail', $scheduleTasks, 200);
    }

    public function completedTask(Request $request)
    {
        $data = Schedule::with('task')->whereHas('configuration.assignees', function ($q) use ($request) {
            $q = $q->where('user_id', Auth::id());
            if (isset($request["type"])) {
                $q = $q->where('repeat', $request["type"]);
            }
        });

        if (isset($request["type"])) {
            switch ($request["type"]) {
                case "weekly":
                    $data = $data->where('created_at', '>=', now()->subWeek())
                        ->where('created_at', '<=', now());
                    break;
                case "monthly":
                    $data = $data->where('created_at', '>=', now()->subMonth())
                        ->where('created_at', '<=', now());
                    break;
                case "yearly":
                    $data = $data->where('created_at', '>=', now()->subYear())
                        ->where('created_at', '<=', now());
                    break;
                case "daily":
                    $data = $data->whereDate('created_at', date('Y-m-d'));
                    break;
            }
        } else {
            // If no type is specified, return an empty array
            return $this->response('Schedule detail', [], 200);
        }

        $data = $data->whereIn('status', ['verified'])->get();
        return $this->response('Schedule detail', $data, 200);
    }

    private function taskList($request, array $status)
    {
        $daily = $this->getTasks("daily", $status);
        $weekly = $this->getTasks("weekly", $status);
        $monthly = $this->getTasks("monthly", $status);
        $yearly = $this->getTasks("yearly", $status);
        $due = $this->getTasks("due", ['in_complete']);

        return [
            'daily' => $daily,
            'weekly' => $weekly,
            'monthly' => $monthly,
            'yearly' => $yearly,
            'due' => $due,
        ];
    }

    public function scheduleTasksMonthly(Request $request)
    {
        $configs = Config::where('repeat', $request['peroid'])->get();

        $config_ids = [];
        foreach ($configs as $config) {
            $schedule = Schedule::where('config_id', $config->id)->count();
            if ($config->repeat_start_dd < date('Y-m-d')) {
                $date = Carbon::createFromFormat('Y-m-d', $config->repeat_start_dd);
                $date->addMonths($schedule);
                array_push($config_ids, $config->id);
            }
        }

        $scheduleTasks = Schedule::with([
            'answers.photo',
            'task.category',
            'configuration.assignees.photo',
            'task.sections.questions.risk',
            'task.sections.questions.fields',
            'task.sections.questions.sub_questions.fields',
        ])
            ->whereHas('configuration.assignees', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->where(function ($query) use ($config_ids) {
                $query->orWhere('config_id', $config_ids);
            })
            ->whereIn('status', ['due', 'in_progress'])->get();

        return $this->response('Schedule detail', $scheduleTasks, 200);
    }

    public function historyTasks(FetchHistorySchedule $request)
    {
        $data = Schedule::with([
            'answers.photo',
            'task.category',
            'configuration.staff.photo',
            'configuration.approvers.photo',
            'task.sections.questions.fields',
            'task.sections.questions.sub_questions.fields',
        ])
            ->whereHas('configuration.assignees', function ($q) {
                $q->where('user_id', Auth::id());
            });

        if (isset($request->category_id) && $request->category_id) {
            $data = $data->whereHas('task', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        if (isset($request->date_to) && $request->date_to) {
            $data = $data->whereDate('updated_at', '<=', $request->date_to);
        }

        if (isset($request->date_from) && $request->date_from) {
            $data = $data->whereDate('updated_at', '>=', $request->date_from);
        }

        $data = $data->get();

        return $this->response('Schedule detail', $data, 200);
    }

    public function getPinnedTask(string $id)
    {
        $scheduleTask = Schedule::with([
            'answers',
            'task.category',
            'configuration.staff.photo',
            'configuration.approvers.photo',
            'task.sections.questions.risk',
            'task.sections.questions.fields',
            'task.sections.questions.sub_questions.fields',
            'task.sections.questions.sub_questions.answer',
        ])->where('id', $id)->whereDate('created_at', date('Y-m-d'))->first();
        return $this->response('Schedule task', $scheduleTask, 200);
    }

    private function getTasks($type, array $status)
    {
        $data = Schedule::with([
            'answers.photo',
            'task.category',
            'answers.user.photo',
            'configuration.staff.photo',
            'configuration.approvers.photo',
            'task.sections.questions.risk.reason.user',
            'task.sections.questions.fields',
            'task.sections.questions.sub_questions.fields',
        ])->whereHas('configuration.staff', function ($q) use ($type) {
            $q = $q->where('user_id', Auth::id());
            if (isset($type) && $type != "due") {
                $q = $q->where('repeat', $type);
            }
        });

        if (isset($type)) {
            switch ($type) {
                case "weekly":
                    $data = $data->where('created_at', '>=', now()->subWeek())
                        ->where('created_at', '<=', now());
                    break;
                case "monthly":
                    $data = $data->where('created_at', '>=', now()->subMonth())
                        ->where('created_at', '<=', now());
                    break;
                case "yearly":
                    $data = $data->where('created_at', '>=', now()->subYear())
                        ->where('created_at', '<=', now());
                    break;
                case "daily":
                    $data = $data->whereDate('created_at', date('Y-m-d'));
                    break;
                case "due":
                    return $data = $data->whereDate('due_date', '<', date('Y-m-d'))->whereIn('status', $status)->paginate(10);
            }
        } else {
            return $data = [];
        }

        $data = $data->whereIn('status', $status)->get();
        return $data;
    }
}
