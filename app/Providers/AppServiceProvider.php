<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\Admin\CompanyIpAddress;
use App\Models\Admin\Team;
use App\Models\Config;
use App\Models\CqcVisit;
use App\Models\Manager\Employee;
use App\Models\QuestionRisk;
use App\Models\Schedule;
use App\Models\Task;
use App\Models\User;
use App\Observers\PlanableObserver;
use App\Services\RoleService;
use App\Services\TeamService;
use App\Services\UserService;
use App\Services\MediaService;
use App\Services\CompanyService;
use App\Services\CategoryService;
use App\Services\ScheduleService;
use App\Services\DashboardService;
use App\Services\MythBusterService;
use App\Services\PermissionService;
use App\Services\PackagePlanService;
use Illuminate\Pagination\Paginator;
use App\Services\NotificationService;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Schema;
use App\Services\CompanyIpAddressService;
use App\Services\Interfaces\RoleInterface;
use App\Services\Interfaces\TeamInterface;
use App\Services\Interfaces\UserInterface;
use App\Services\Interfaces\ScheduleInterface;
use App\Services\Interfaces\DashboardInterface;
use App\Services\Interfaces\MythBusterInterface;
use App\Services\Interfaces\PermissionInterface;
use App\Services\Interfaces\PackagePlanInterface;
use App\Services\Interfaces\MediaServiceInterface;
use App\Services\Interfaces\NotificationInterface;
use App\Services\Interfaces\SubscriptionInterface;
use App\Services\Interfaces\CompanyServiceInterface;
use App\Services\Interfaces\CategoryServiceInterface;
use App\Services\Interfaces\CompanyIpAddressInterface;
use App\Services\Interfaces\ThemeSettingInterface;
use App\Services\ThemeSettingService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CategoryServiceInterface::class, CategoryService::class);
        $this->app->bind(CompanyServiceInterface::class, CompanyService::class);
        $this->app->bind(MediaServiceInterface::class, MediaService::class);
        $this->app->bind(RoleInterface::class, RoleService::class);
        $this->app->bind(PermissionInterface::class, PermissionService::class);
        $this->app->bind(UserInterface::class, UserService::class);
        $this->app->bind(CompanyIpAddressInterface::class, CompanyIpAddressService::class);
        $this->app->bind(TeamInterface::class, TeamService::class);
        $this->app->bind(NotificationInterface::class, NotificationService::class);
        $this->app->bind(DashboardInterface::class, DashboardService::class);
        $this->app->bind(PackagePlanInterface::class, PackagePlanService::class);
        $this->app->bind(ScheduleInterface::class, ScheduleService::class);
        $this->app->bind(SubscriptionInterface::class, SubscriptionService::class);
        $this->app->bind(MythBusterInterface::class, MythBusterService::class);
        $this->app->bind(ThemeSettingInterface::class, ThemeSettingService::class);

        Team::observe(PlanableObserver::class);
        Task::observe(PlanableObserver::class);
        User::observe(PlanableObserver::class);
        Config::observe(PlanableObserver::class);
        QuestionRisk::observe(PlanableObserver::class);
        Schedule::observe(PlanableObserver::class);
        CqcVisit::observe(PlanableObserver::class);
        Employee::observe(PlanableObserver::class);
        CompanyIpAddress::observe(PlanableObserver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
    }
}
