<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Schedule;
use App\Models\QuestionRisk;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class ScheduleService
 * @package App\Services
 */
class AuditService
{

    public function __construct(private GeneralService $generalService)
    {
        // parent::__construct($model);
    }

    public function index($request)
    {
        $relationalCols = [
            'task' => ['name'],
        ];
        $companyId = auth()->user()->company_id;

        $data['total_schedules']        = Schedule::where('company_id', $companyId)->where('status', '!=', 'verified')->count();
        $data['rejected_schedules']     = Schedule::where('company_id', $companyId)->whereStatus('rejected')->count();
        $data['in_progress_schedules']  = Schedule::where('company_id', $companyId)->whereStatus('in_progress')->count();
        $data['total_risks']            = QuestionRisk::whereCompanyId($companyId)->where('status', '!=', 'completed')->count();
        $data['requested_risks']        = QuestionRisk::whereCompanyId($companyId)->whereStatus('draft')->count();
        $data['in_completed_risks']     = QuestionRisk::whereCompanyId($companyId)->whereStatus('in_progress')->count();
        // $data['in_complete_schedules'] = Schedule::where('company_id', $companyId)->whereStatus('in_progress')->count();


        return $data;
        
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
