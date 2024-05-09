<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Models\Config;
use App\Models\CqcVisit;
use App\Models\Schedule;
use App\Models\Admin\Team;
use App\Models\QuestionRisk;
use App\Models\Admin\Category;
use App\Models\ScheduleAnswer;
use App\Models\Admin\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin\CompanyIpAddress;
use App\Services\Interfaces\DashboardInterface;

/**
 * Class UserService
 * @package App\Services
 */
class DashboardService implements DashboardInterface
{
    public function __construct(private GeneralService $generalService)
    {
        $this->generalService = $generalService;
        // parent::__construct($model);
    }

    public function getStats()
    {
        $loggeduserRole =  Auth::user()->roles->pluck('name')[0];

        $cheklist_and_config_ids = Config::whereHas('task', function ($q) {
            if (auth()->user()->roles->pluck('name')[0] == 'Super Admin') {
                return $q->whereIn('type', ['admin_template']);
            } else if (auth()->user()->roles->pluck('name')[0] == 'Manager') {
                return $q->whereIn('type', ['admin_template', 'custom_template']);
            }
        });

        // Add a condition to filter by company_id if the user role is Manager
        if ($loggeduserRole == 'Manager') {
            $companyId = auth()->user()->company_id;
            $cheklist_and_config_ids->where('company_id', $companyId);
            $scheduleQuery = Schedule::whereCompanyId($companyId);

            $clones = [
                'completedTasksWithNoRiskQuery',
                'completedTasksWithRiskQuery',
                'completedTasksWithRemainingRequiredAnsQuery',
                'graphQuery',
                'dailyChecklistQuery',

                'completedChecklistQuery',
                'totalDueChecklistsQuery',
                'totalHighDueChecklistQuery',
                'totalMediumDueChecklistQuery',
                'totalLowDueChecklistQuery',
                'totalHighCompletedChecklistQuery',
                'totalMediumCompletedChecklistQuery',
                'totalLowCompletedChecklistQuery',
            ];

            foreach ($clones as $cloneName) {
                ${$cloneName} = clone $scheduleQuery;
            }
        }

        // Select specific columns and get the result as an array
        $cheklist_and_config_ids = $cheklist_and_config_ids->select('id', 'task_id')->get()->toArray();


        $checklistIds = array();
        $checklistConfigIds = array();
        foreach ($cheklist_and_config_ids as $item) {
            array_push($checklistConfigIds, $item['id']);
            array_push($checklistIds, $item['task_id']);
        }


        $recentChecklistReport = [];
        $completedTasksWithNoRisk = 0;
        $completedTasksWithRisk = 0;
        $taskStatsMonthly = [];
        $completedTasksWithRemainingRequiredAns = 0;
        $upCommingCqcVisit = null;

        if ($loggeduserRole == 'Manager') {
            $upCommingCqcVisit = CqcVisit::whereCompanyId($companyId)
                ->where(function ($query) {
                    $query->where('visit_date', '>', Carbon::now()->format('Y-m-d H:i'));
                    // ->orWhere(function ($query) {
                    //     $query->whereDate('visit_date', Carbon::today())
                    //         ->whereTime('visit_date', '>', Carbon::now()->format('H:i:s'));
                    // });
                })
                ->first();
            // dd(Carbon::now()->format('Y-m-d H:i'));
            $riskCounts = $this->getRiskCounts($companyId);
            $recentChecklistReport = Notification::whereIn('type_id', $checklistConfigIds)
                ->whereType('App\Models\Config')
                ->with('notifiable', 'typeable')
                ->where('notifiable_id', '!=', auth()->user()->id)
                ->latest()->take(4)->get();

            $completedTasksWithNoRisk = $completedTasksWithNoRiskQuery
                ->whereStatus('verified')
                ->whereDoesntHave('task.sections.questions.risk')
                ->count();
            $completedTasksWithRisk =    $completedTasksWithRiskQuery
                ->whereStatus('verified')
                ->whereHas('task.sections.questions.risks')
                ->count();

            $completedTasksWithRemainingRequiredAns = $this->getTaskCountWithRemainingRequiredAnswer($completedTasksWithRemainingRequiredAnsQuery, $companyId);
            $taskStatsMonthly =    $this->getMonthlyTaskStats($graphQuery, $companyId);
        }


        $data = [
            'overAllChecklists'         => $dailyChecklistQuery->whereIn('status', ['verified', 'in_progress'])->count(),
            'totalCompletedChecklists'  => $completedChecklistQuery->whereStatus('verified')->count(),
            'totalDueChecklists'        => $totalDueChecklistsQuery->whereStatus('in_progress')->count(),
            'totalHighDueChecklists'    => $totalHighDueChecklistQuery->whereStatus('in_progress')
                ->whereHas('task', function ($q) {
                    $q->where('priority', 'high');
                })
                ->count(),
            'totalMediumDueChecklists'   => $totalMediumDueChecklistQuery
                ->whereStatus('in_progress')
                ->whereHas('task', function ($q) {
                    $q->where('priority', 'medium');
                })
                ->count(),
            'totalLowDueChecklists'  => $totalLowDueChecklistQuery
                ->whereStatus('in_progress')
                ->whereHas('task', function ($q) {
                    $q->where('priority', 'low');
                })
                ->count(),
            'totalHighCompletedChecklists'  => $totalHighCompletedChecklistQuery
                ->whereStatus('verified')
                ->whereHas('task', function ($q) {
                    $q->where('priority', 'high');
                })
                ->count(),
            'totalMediumCompletedChecklists'   => $totalMediumCompletedChecklistQuery
                ->whereStatus('verified')
                ->whereHas('task', function ($q) {
                    $q->where('priority', 'medium');
                })
                ->count(),
            'totalLowCompletedChecklists'   => $totalLowCompletedChecklistQuery
                ->whereStatus('verified')
                ->whereHas('task', function ($q) {
                    $q->where('priority', 'low');
                })
                ->count(),
            'riskCounts'                    => $riskCounts,
            'completedTasksWithNoRisk'      => $completedTasksWithNoRisk,
            'completedTasksWithRisk'        => $completedTasksWithRisk,
            'taskStatsMonthly'              => $taskStatsMonthly,


            'completedTasksWithRemainingRequiredAns'        => $completedTasksWithRemainingRequiredAns,


            'recentChecklistReport'     => $recentChecklistReport,
            'upCommingCqcVisit'         => $upCommingCqcVisit,
        ];

        $data['categoryStats'] = $this->getCategoryStats($scheduleQuery, $checklistIds);

        if (intval($data['overAllChecklists']) > 0) {
            $data['completedTasksPercentage'] = round((intval($data['totalCompletedChecklists'])  / intval($data['overAllChecklists'])) * 100);
            $data['dueTasksPercentage']         = round((intval($data['totalDueChecklists'])  / intval($data['overAllChecklists'])) * 100);
        }

        return $data;
    }

    private function getRiskCounts($companyId)
    {
        $riskCounts = QuestionRisk::whereCompanyId($companyId)
            ->selectRaw('
                SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as draftRisks,
                SUM(CASE WHEN status = "in_progress" AND due_date > DATE(NOW()) THEN 1 ELSE 0 END) as dueRisks,
                SUM(CASE WHEN status = "completed"  THEN 1 ELSE 0 END) as completedRisks,
                SUM(CASE WHEN status = "in_progress" AND priority = "low" THEN 1 ELSE 0 END) as lowDraftRisks,
                SUM(CASE WHEN status = "in_progress" AND priority = "medium" THEN 1 ELSE 0 END) as mediumDraftRisks,
                SUM(CASE WHEN status = "in_progress" AND priority = "high" THEN 1 ELSE 0 END) as highDraftRisks,
                SUM(CASE WHEN status = "in_progress" AND priority = "low" AND due_date > DATE(NOW()) THEN 1 ELSE 0 END) as lowDueRisks,
                SUM(CASE WHEN status = "in_progress" AND priority = "medium" > DATE(NOW()) THEN 1 ELSE 0 END) as mediumDueRisks,
                SUM(CASE WHEN status = "in_progress" AND priority = "high" > DATE(NOW()) THEN 1 ELSE 0 END) as highDueRisks,
                SUM(CASE WHEN status = "completed" AND priority = "low" THEN 1 ELSE 0 END) as lowCompletedRisks,
                SUM(CASE WHEN status = "completed" AND priority = "medium" THEN 1 ELSE 0 END) as mediumCompletedRisks,
                SUM(CASE WHEN status = "completed" AND priority = "high" THEN 1 ELSE 0 END) as highCompletedRisks
            ')
            ->first();

        return $riskCounts;
    }

    private function getMonthlyTaskStats($graphQuery, $companyId)
    {
        // $startTime = microtime(true);
        $currentYear = now()->year;

        $stats = $graphQuery->selectRaw('
            YEAR(created_at) as year, 
            MONTH(created_at) as month, 
            SUM(CASE WHEN status = "verified" THEN 1 ELSE 0 END) as completed_count,
            SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as due_count,
            SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected_count
        ')
            ->whereYear('created_at', $currentYear)
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->get();

        $result = [];
        $result['monthlyCompletedChecklists']   = $this->formatResult($stats, 'completed_count');
        $result['monthlyDueChecklists']         = $this->formatResult($stats, 'due_count');
        $result['monthlyRejectedChecklists']    = $this->formatResult($stats, 'rejected_count');

        // $endTime = microtime(true);
        // $executionTime = $endTime - $startTime;
        // info("Graph Query took " . $executionTime . " seconds to execute.");

        return $result;
    }

    private function formatResult($stats, $columnName)
    {
        $allMonths = [
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
        ];

        foreach ($allMonths as $month) {
            $result[] = ['month' => $month, 'count' => 0];
        }

        foreach ($stats as $stat) {
            $monthName = date('M', mktime(0, 0, 0, $stat->month, 1));

            $index = array_search($monthName, array_column($result, 'month'));

            $result[$index]['count'] = $stat->{$columnName};
        }

        return array_column($result, 'count');
    }


    private function getTaskCountWithRemainingRequiredAnswer($query, $companyId)
    {
        $scheduleData = $query->where('status', 'verified')
            ->whereHas('task.sections.questions.fields')
            ->with(['task.sections.questions.fields', 'answers'])
            ->get();

        $scheduleIds = [];

        $scheduleData->each(function ($schedule) use ($scheduleIds) {
            $requiredFields = $schedule->task->sections->flatMap->questions->flatMap->fields
                ->where('pivot.required', true);

            $allFields = $requiredFields->pluck('id')->all();

            $answerCount = $schedule->answers->whereIn('pivot_field_id', $allFields)
                ->whereNotIn('answer', ['in-active', null, ''])
                ->count();

            if ($answerCount < count($requiredFields)) {
                $scheduleIds[] = $schedule->id;
            }
        });

        $uniqueScheduleIds = array_unique($scheduleIds);

        return count($uniqueScheduleIds);
    }




    private function getCategoryStats($scheduleQuery, $checklistIds)
    {

        $dailyChecklists = $scheduleQuery->where('status', 'verified')
            ->whereHas('task')
            ->get();

        $groupedLists = $dailyChecklists->groupBy('task.category_id');

        if (count($groupedLists) < 4) {

            $categoryIds = Task::whereIn('id', $checklistIds)->pluck('category_id')->unique();

            if (count($categoryIds) > 0) {
                $categories = Category::whereIn('id', $categoryIds)
                    ->whereNull('parent_id')
                    ->when(count($categoryIds) < 4, function ($query) use ($categoryIds) {
                        // If the count of $categoryIds is less than 4, include additional categories
                        $remainingCategories = Category::whereNull('parent_id')
                            ->whereNotIn('id', $categoryIds)
                            ->take(4 - count($categoryIds))
                            ->pluck('id');
                        return $query->orWhereIn('id', $remainingCategories);
                    })
                    ->take(4)
                    ->get();
            } else {
                $categories = Category::whereNull('parent_id')->take(4)->get();
            }
            $randomStats = [];
            foreach ($categories as $category) {
                array_push($randomStats, [
                    'category_id'   => $category->id,
                    'name'          => $category->name,
                    'icon_path'     => $category->photo?->path,
                    'count'         => 0,
                ]);
            }
        }
        $categoryStats = $groupedLists->map(function ($group) {
            return [
                'category_id'   => $group->first()->task->category_id,
                'name'          => $group->first()->task->category?->name,
                'icon_path'     => $group->first()->task->category?->photo?->path,
                'count'         => $group->count(),
            ];
        })->values()->slice(0, 4)->toArray();


        $finalStats = [];

        if (count($categoryStats) < 4) {

            $mergedStats = array_merge($categoryStats, $randomStats);
            $uniqueArray = collect($mergedStats)->unique('category_id')->values()->all();
            $finalStats = array_slice($uniqueArray, 0, 4);
        }

        return $finalStats;
    }


    public function getRiskStats()
    {
        $companyId = auth()->user()->company_id;

        $riskChecklistIds = Task::where('type', 'risk_template')
            ->where('company_id', $companyId)
            ->pluck('id');

        $data = [
            'totalDailyChecklist' => Schedule::whereIn('task_id', $riskChecklistIds)->count(),
            'totalTodayVerifiedDailyChecklist' => Schedule::whereIn('task_id', $riskChecklistIds)->whereDate('created_at', Carbon::today())->where('status', 'verified')->count(),
            'totalTodayDailyChecklist' => Schedule::whereIn('task_id', $riskChecklistIds)->whereDate('created_at', Carbon::today())->count(),
            'totalPendingDailyChecklist' => Schedule::whereIn('task_id', $riskChecklistIds)->where('status', 'in_progress')->count(),
            'totalPendingApprovedRejected' => Schedule::whereIn('task_id', $riskChecklistIds)->whereIn('status', ['in_progress', 'verified', 'rejected'])->count(),
            'totalVerifiedDailyChecklist' => Schedule::whereIn('task_id', $riskChecklistIds)->where('status', 'verified')->count(),
            'totalActiveRisks' => Task::where('status', 'active')->whereIn('id', $riskChecklistIds)->count(),
            'totalDraftRisks' => Task::where('status', 'draft')->whereIn('id', $riskChecklistIds)->count(),
            'totalRisks' => count($riskChecklistIds),
        ];

        return $data;
    }

    public function getCurrentLevelRiskStats($request)
    {
        $companyId = auth()->user()->company_id;

        $lowRiskQuery = Task::where('type', 'risk_template')
            ->where('company_id', $companyId);


        $startDate = $this->convertArrayToDate($request->from);
        $endDate = $this->convertArrayToDate($request->to);

        if ($startDate && $endDate) {
            $lowRiskQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $clones = [
            'mediumRiskQuery',
            'highRiskQuery'
        ];

        foreach ($clones as $cloneName) {
            ${$cloneName} = clone $lowRiskQuery;
        }

        $data = [
            'lowRisksCount'         => $lowRiskQuery->where('priority', 'low')->count(),
            'mediumRisksCount'      => $mediumRiskQuery->where('priority', 'medium')->count(),
            'highRisksCount'        => $highRiskQuery->where('priority', 'high')->count(),
        ];

        return $data;
    }

    private function convertArrayToDate($dateArray)
    {
        if (
            isset($dateArray['year']) &&
            isset($dateArray['month']) &&
            isset($dateArray['day'])
        ) {
            return Carbon::create(
                $dateArray['year'],
                $dateArray['month'],
                $dateArray['day']
            )->toDateString();
        }

        return null;
    }


    public function getRisksData($request)
    {
        $companyId = auth()->user()->company_id;

        if ($request->staff_ids) {
            $checklistConfigIds  =  DB::table('config_assignees')->whereIn('user_id', $request->staff_ids)->pluck('config_id');
            $checklistIds        =  DB::table('configs')->whereIn('id', $checklistConfigIds)->pluck('task_id');
        } else {
            $checklistIds = Task::where('type', 'risk_template')
                ->where('company_id', $companyId)->whereHas('schedules')->pluck('id');
        }

        $from   = $request->risk_date_range['from'];
        $to     = $request->risk_date_range['to'];

        if ($request->category_id || $request->staff_ids) {

            $checklistQuery = Schedule::whereIn('task_id', $checklistIds)
                ->with('task');

            if (!is_null($request->category_id)) {
                $checklistQuery->whereHas('task', function ($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                });
            }

            if (!is_null($from) && !is_null($to) && $request->selected_tab != 'today') {
                $checklistQuery->whereBetween('created_at', [$from, $to]);
            }

            if ($request->selected_tab == 'total_daily_risks') {
                $data['totalDailyRisks'] = $checklistQuery->get();
            } elseif ($request->selected_tab == 'today') {
                $data['todayDailyRisks'] = $checklistQuery->whereDate('created_at', Carbon::today())->get();
            } elseif ($request->selected_tab == 'pending') {
                $data['pendingDailyRisks'] = $checklistQuery->whereStatus('in_progress')->get();
            } elseif ($request->selected_tab == 'approved') {
                $data['approvedDailyRisks'] = $checklistQuery->whereStatus('verified')->get();
            }

            return $data;
        }

        $data['totalDailyRisks'] = Schedule::whereIn('task_id', $checklistIds)->with('task')->get();
        $data['todayDailyRisks'] = Schedule::whereIn('task_id', $checklistIds)
            ->whereDate('created_at', Carbon::today())->with('task')->get();
        $data['pendingDailyRisks'] = Schedule::whereIn('task_id', $checklistIds)
            ->whereStatus('in_progress')->with('task')->get();

        $data['approvedDailyRisks'] = Schedule::whereIn('task_id', $checklistIds)
            ->whereStatus('verified')->with('task')->get();

        return $data;
    }
}
