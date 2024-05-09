<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Models\Config;
use App\Models\Admin\Team;
use App\Models\PackagePlan;
use App\Models\CompanyPackagePlan;
use App\Models\QuestionRisk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin\CompanyIpAddress;
use App\Services\Interfaces\SubscriptionInterface;

/**
 * Class SubscriptionService
 * @package App\Services
 */
class SubscriptionService  implements SubscriptionInterface
{
    public function __construct(private CompanyPackagePlan $model, private GeneralService $generalService)
    {
    }
    
    

    public function store($validated)
    {
        $planType = PackagePlan::whereId($validated['package_plan_id'])->pluck('plan_type')->first();
        $currentDate = Carbon::now();

        switch ($planType) {
            case 'weekly':
                $expiryDate = $currentDate->addDay(7);
                break;
            case 'quaterly-year':
                $expiryDate = $currentDate->addMonths(3);
                break;
            case 'half-year':
                $expiryDate = $currentDate->addMonths(6);
                break;
            case 'monthly':
                $expiryDate = $currentDate->addMonth();
                break;
            case 'yearly':
                $expiryDate = $currentDate->addYear();
                break;
            default:
                $expiryDate = $currentDate->addMonth(); // Set a default to one month if the plan type is unknown
                break;
        }

        $validated['is_active'] = 1;
        $validated['subscribe_date'] = date('Y-m-d');
        $validated['expire_date'] = $expiryDate;
        $package = CompanyPackagePlan::create($validated);
        return $package;
    }

    public function getSubscribedPlanDetail()
    {
        $companyId = auth()->user()->company_id;
        
        $planUsage = PackagePlan::join('company_package_plans', 'package_plans.id', '=', 'company_package_plans.package_plan_id')
            ->leftJoin('tasks', function ($join) use ($companyId) {
                $join->on('tasks.company_id', '=', 'company_package_plans.company_id')
                    ->whereIn('tasks.type', ['admin_template', 'custom_template'])
                    ->where('tasks.status', 'active');
            })
            ->leftJoin('question_risks', 'question_risks.company_id', '=', 'company_package_plans.company_id')
            ->leftJoin('configs', 'configs.company_id', '=', 'company_package_plans.company_id')
            ->leftJoin('company_ip_addresses', function ($join) use ($companyId) {
                $join->on('company_ip_addresses.company_id', '=', 'company_package_plans.company_id')
                    ->where('company_ip_addresses.is_active', true);
            })
            ->leftJoin('teams', function ($join) use ($companyId) {
                $join->on('teams.company_id', '=', 'company_package_plans.company_id')
                    ->where('teams.is_active', true);
            })
            ->leftJoin('users', 'users.company_id', '=', 'company_package_plans.company_id')
            ->where('company_package_plans.company_id', $companyId)
            ->where('company_package_plans.expire_date', '>=', Carbon::now())
            ->where('company_package_plans.is_active', true)
            ->select(
                'package_plans.*',
                DB::raw('COUNT(DISTINCT tasks.id) as total_checklists'),
                DB::raw('COUNT(DISTINCT question_risks.id) as total_risks'),
                DB::raw('COUNT(DISTINCT configs.id) as total_configs'),
                DB::raw('COUNT(DISTINCT company_ip_addresses.id) as total_active_ips'),
                DB::raw('COUNT(DISTINCT teams.id) as total_teams'),
                DB::raw('COUNT(DISTINCT users.id) as total_users')
            )
            ->groupBy('package_plans.id')
            ->get();
        return  $planUsage;
    }
    
    public function subscribedPlanCompanies($request, $id) {

        $data = CompanyPackagePlan::with('company')->where('package_plan_id',$id);

        if ($request['perpage'] == 'All') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            return $this->generalService->handlePagination($request, $data);
        }
    }

}
