<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Admin\Notification;
use App\Models\CqcVisit;
use App\Models\CheckIn;
use App\Models\QuestionRisk;
use App\Models\Schedule;
use App\Services\GeneralService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $today = Carbon::today()->format('Y-m-d');
        $pending_tasks = Schedule::with(['task', 'configuration', 'configuration.assignees'])
            ->whereHas('configuration.assignees', function ($q) {
                $q->where('user_id', Auth::id());
            })
            // ->whereHas('task', function ($q) {
            //     $q->whereIn('type', ['custom_template', 'admin_template']);
            // })
            ->whereDate('created_at', $today)
            ->whereIn('status', ['due', 'in_progress', 'requested'])
            ->count();

        $verified_tasks = Schedule::with(['task', 'configuration', 'configuration.assignees'])
            ->whereHas('configuration.assignees', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->whereHas('task', function ($q) {
                $q->whereIn('type', ['custom_template', 'admin_template']);
            })
            ->whereDate('created_at', $today)
            ->where('status', 'verified')
            ->count();

        $pending_risks = QuestionRisk::with(['assignees'])
            ->whereHas('assignees', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->whereDate('due_date', '>=', $today)
            ->where('status', 'in_progress')
            ->count();

        $outdated_risks = QuestionRisk::with(['assignees'])
            ->whereHas('assignees', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->whereDate('due_date', '<', $today)
            ->where('status', 'in_progress')
            ->count();

        $notifications = Notification::where('notifiable_id', auth()->user()->id)->groupBy('type_id')->orderBy('created_at', 'desc')->take(3)->get();

        if (isset($request["pinnedTask"]) && $request["pinnedTask"]) {
            $pinnedTask = Schedule::with('task.category')->where('id', $request["pinnedTask"])->first();
        }

        $cqc_visit = CqcVisit::whereCompanyId(Auth::user()->company_id)->first();
        $today = Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'));

        if ($cqc_visit && $cqc_visit->visit_date >= date('Y-m-d H:i:s')) {
            $cqc_date = Carbon::createFromFormat('Y-m-d H:i:s', $cqc_visit->visit_date);
            $days_left = $cqc_date->diffInDays($today);
        } else {
            $days_left = -1;
        }

        $attendance = CheckIn::where('user_id', auth()->user()->id)->whereDate('created_at', $today)->orderBy('created_at', 'desc')->first();
        $time_logs = CheckIn::where('user_id', auth()->user()->id)->whereDate('created_at', $today)->get();

        $data = [
            'pending_tasks' => $pending_tasks,
            'verified_tasks' => $verified_tasks,
            'pending_risks' => $pending_risks,
            'outdated_risks' => $outdated_risks,
            'notifications' => $notifications,
            'pinnedTask' => $pinnedTask ?? (object)[],
            'attendance' => $attendance ?? (object)[],
            'time_logs' => $time_logs,
            'days_left' => $days_left,
        ];

        return $this->response('Dashboard stats', $data, 200);
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
        //
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
}
