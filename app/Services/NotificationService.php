<?php

namespace App\Services;

use App\Services\Interfaces\NotificationInterface;
use App\Models\Admin\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Class NotificationService
 * @package App\Services
 */
class NotificationService implements NotificationInterface
{
    public function __construct(private Notification $model, private GeneralService $generalService)
    {
        // parent::__construct($model);
    }


    public function index($request)
    {
        $data = Notification::query();

        $user = Auth::user();
        $role =  $user->roles->pluck('name')[0];

        if ($role == 'Super Admin') {
            $data = $data->whereNull('company_id');
        } else if ($role == 'Manager') {
            $managers = $user->company->managers->pluck('id')->toArray();
            $data = $data->where('notifiable_id', $managers);
        } else {
            $data = $data->where('notifiable_id', auth()->user()->id);
        }

        $data = $data->take(10)->orderBy('created_at', 'desc')->get();

        return $data;
    }

    public function notificationList($request)
    {
        $user = Auth::user();
        $role =  $user->roles->pluck('name')[0];

        if ($role == 'Super Admin') {
            $res = $this->superAdminList();
        } else  if (
            $role == 'Manager'
        ) {
            $res = $this->managerList();
        } else if ($role == 'Staff') {
            $res = $this->staffList();
        } else {
            $res = $this->notificaiotnList();
        }

        $data = [
            'today' => $res['today']->groupBy('type_id')->orderBy('created_at', 'desc')->get(),
            'yesterday' => $res['yesterday']->groupBy('type_id')->orderBy('created_at', 'desc')->get(),
            'week' => $res['week']->groupBy('type_id')->orderBy('created_at', 'desc')->get(),
            'month' => $res['month']->groupBy('type_id')->orderBy('created_at', 'desc')->get(),
        ];

        return $data;
    }

    public function superAdminList()
    {
        $today = Notification::today()->whereNull('company_id');
        $yesterday = Notification::yesterday()->whereNull('company_id');
        $week = Notification::week()->whereNull('company_id');
        $month = Notification::month()->whereNull('company_id');

        $data = [
            'today' => $today,
            'yesterday' => $yesterday,
            'week' => $week,
            'month' => $month,
        ];

        return $data;
    }

    public function managerList()
    {
        $user = Auth::user();
        $first_date = date('Y-m-d', strtotime("this week"));
        $last_date = date("Y-m-d", strtotime(date("Y-m-d", strtotime("this week")) . " +6 days"));
        $managers = $user->company->managers->pluck('id')->toArray();

        $today = Notification::today()->whereIn('notifiable_id', $managers);
        $yesterday = Notification::yesterday()->where('notifiable_id', $managers);
        $week = Notification::whereDate('created_at', '!=', date('Y-m-d'))
            ->whereDate('created_at', '!=', date("Y-m-d", strtotime("yesterday")))
            ->week()->where('notifiable_id', $managers);
        $month = Notification::month()->where('notifiable_id', $managers)
            ->whereDate('created_at', '!=', date('Y-m-d'))
            ->whereDate('created_at', '!=', date("Y-m-d", strtotime("yesterday")))->whereNotBetween('created_at', [$first_date, $last_date]);

        $data = [
            'today' => $today,
            'yesterday' => $yesterday,
            'week' => $week,
            'month' => $month,
        ];

        return $data;
    }

    public function staffList()
    {
        $first_date = date('Y-m-d', strtotime("this week"));
        $last_date = date("Y-m-d", strtotime(date("Y-m-d", strtotime("this week")) . " +6 days"));

        $today = Notification::today()->where('notifiable_id', auth()->user()->id);
        $yesterday = Notification::yesterday()->where('notifiable_id', auth()->user()->id);
        $week = Notification::week()
            ->whereDate('created_at', '!=', date('Y-m-d'))
            ->whereDate('created_at', '!=', date("Y-m-d", strtotime("yesterday")))
            ->where('notifiable_id', auth()->user()->id);
        $month = Notification::month()->where('notifiable_id', auth()->user()->id)->whereDate('created_at', '!=', date('Y-m-d'))
            ->whereDate('created_at', '!=', date("Y-m-d", strtotime("yesterday")))->whereNotBetween('created_at', [$first_date, $last_date]);

        $data = [
            'today' => $today,
            'yesterday' => $yesterday,
            'week' => $week,
            'month' => $month,
        ];

        return $data;
    }

    public function notificaiotnList()
    {
        $first_date = date('Y-m-d', strtotime("this week"));
        $last_date = date("Y-m-d", strtotime(date("Y-m-d", strtotime("this week")) . " +6 days"));

        $today = Notification::today()->where('notifiable_id', auth()->user()->id);
        $yesterday = Notification::yesterday()->where('notifiable_id', auth()->user()->id);
        $week = Notification::week()
            ->whereDate('created_at', '!=', date('Y-m-d'))
            ->whereDate('created_at', '!=', date("Y-m-d", strtotime("yesterday")))
            ->where('notifiable_id', auth()->user()->id);
        $month = Notification::month()->where('notifiable_id', auth()->user()->id)->whereDate('created_at', '!=', date('Y-m-d'))
            ->whereDate('created_at', '!=', date("Y-m-d", strtotime("yesterday")))->whereNotBetween('created_at', [$first_date, $last_date]);

        $data = [
            'today' => $today,
            'yesterday' => $yesterday,
            'week' => $week,
            'month' => $month,
        ];

        return $data;
    }
}
