<?php

namespace App\Http\Middleware;

use App\Models\Admin\Team;
use App\Models\CompanyPackagePlan;
use App\Models\PackagePlan;
use App\Models\Task;
use App\Models\User;
use App\Models\Config;
use App\Models\Admin\CompanyIpAddress;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

class CheckStorageLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $action = $request->method();
        $user = auth()->user();
        if ($action == 'POST') {
            $route = Route::currentRouteName();
            
            if ($route == 'team.store' || $route == 'user.store' || $route == 'tasks.store' || $route == 'configs.store' || $route == 'ip-address.store') {
                $date = Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
                $grace_day = $date->addDays(15); 
                $active_plan = CompanyPackagePlan::where('company_id', $user->company_id)
                    ->where('expire_date', '>', $grace_day)
                    ->latest('created_at')
                    ->get();

                if ($active_plan->isEmpty()) {
                    abort(403, 'Unauthorized: No active plan is right now.');
                }
                $latestActivePlan = $active_plan->first();
                
                $this->checkLimit('team', 'allow_teams', Team::class, 'Active Team', $latestActivePlan);
                $this->checkLimit('user', 'allow_users', User::class, 'Active User', $latestActivePlan);
                $this->checkLimit('tasks', 'allow_checklist', Task::class, 'Active Task', $latestActivePlan);
                $this->checkLimit('configs', 'allow_checklist_config', Config::class, 'Active Task', $latestActivePlan);
                $this->checkLimit('ip-address', 'allow_active_ip', CompanyIpAddress::class, 'Active IP', $latestActivePlan);
            }
        }

        return $next($request);
    }


    protected function checkLimit($routeKey, $limitKey, $modelClass, $messagePrefix, $active_plan)
    {
        $route = Route::currentRouteName();

        if (in_array($route, ["$routeKey.store", "$routeKey.update"])) {
            $count = $modelClass::where('company_id', auth()->user()->company_id)->count();
            $package_plan = PackagePlan::where('id', $active_plan->package_plan_id)->first();


            if ($package_plan->$limitKey <= $count) {
                abort(403, "Unauthorized: Allow $messagePrefix Limit exceed.");
            }
        }
    }
}
