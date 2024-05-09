<?php

namespace App\Services;

use App\Models\Admin\TeamUser;
use App\Models\Config;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendEmailJob;
use App\Models\ConfigAssignee;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

/**
 * Class ConfigService
 * @package App\Services
 */
class ConfigService
{
    public function __construct(private Config $model, private GeneralService $generalService, private RiskConfigService $riskConfigService, private ConfigNotificationService $configService)
    {
        // parent::__construct($model);
    }

    public function index($request)
    {
        // $whereIn = [
        //     'checklist' => [
        //         // 'type' => ['custom_template'],
        //         'company_id' => [Auth::user()->company_id]
        //     ]
        // ];
        // $where =  [
        //     'checklist' => [
        //         ['key' => 'type', 'operator' => '=', 'value' => 'custom_template'],
        //         // ['key' => 'company_id', 'operator' => '=', 'value' => Auth::user()->company_id],
        //     ]
        // ];
        $searchCols = ['repeat', 'repeat_count'];

        $relationalCols = [
            'task' => ['name'],
        ];

        $data = Config::with(['task', 'company', 'staff', 'approvers', 'previous_checklist_daily', 'task.mythBusters']);

        // Filtering by company_id and type
        // company_id can be null or auth id
        // type will be admin_template and custom_template
        // Start
        $data = $data->where('company_id', Auth::user()->company_id);
        $data = $data->whereHas('task', function ($query) {
            $query->whereIn('company_id', [Auth::user()->company_id])->orWhereNull('company_id');
        });
        $data = $data->whereHas('task', function ($query) {
            $query->whereIn('type', ['admin_template', 'custom_template']);
        });
        // End

        if (isset($request->name)) {
            $data =  $data->whereHas('task', function ($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->name}%");
            });
        };

        if (isset($request->repeat)) {
            $data =  $data->where('repeat', $request->repeat);
        };

        if (isset($request->repeat_start_dd)) {
            $data =  $data->where('repeat_start_dd', $request->repeat_start_dd);
        };
        if (isset($request->is_active)) {
            $data =  $data->where('is_active', $request->is_active);
        };

        $data = $this->generalService->handleSearch($request['searchText'], $data, $searchCols, '', $relationalCols);

        if ($request['perpage'] == 'All') {
            return $this->generalService->handleAllData($request, $data);
        } else {
            return $this->generalService->handlePagination($request, $data);
        }
    }

    public function store($data)
    {
        $config = Config::where('task_id', $data['task_id'])->where('company_id', auth()->user()->company_id)->latest('created_at')->first();
        if ($config) {
            $add_day = $this->configExist(Carbon::createFromFormat('Y-m-d', $config->repeat_start_dd), $config);
            if ($add_day->format('Y-m-d') > date('Y-m-d')) {
                abort(422, "Kindly refrain from duplicating the checklist.Perform the task after the " . $add_day->format('Y-m-d')); //Will throw an HTTP exception with code 403
            }
        }

        $auth = Auth::user();
        $data['user_id'] = $auth->id;
        $data['company_id'] = $auth->company_id;
        $config = Config::create($data);

        \Artisan::call('app:store-daily-checklist');

        $staff = [];
        $task = Task::where('id', $data['task_id'])->first();

        $content = "We trust this message finds you well.<br /><br />I am writing to inform you that a checklist has been assigned to you on MyCheX.<br /><br /> <b>Checklist Details:</b><br /><b>Checklist Title: </b>" . $task->name . '<br />  <b>Peroid Type: </b> ' . $config->repeat . ' <br /><b> Description: </b>' . $task->description . ' <br /><br />Your attention to completing this checklist is highly valued. If you have any questions or require clarification regarding the assigned checklist, please do not hesitate to reach out <a href="mailto:info@mychex.co.uk">info@mychex.co.uk</a>';

        foreach ($data['staff_id'] as $user) {
            $staff[] = [
                'user_id' => $user,
            ];
        }
        $checklist_user = User::whereIn('id', $staff)->get();
        $subject = "Assignment: Checklist on MyCheX";
        $approvalEmailContent = '';
        if ($data['team_id']) {
            $userNotificationContent = $config->team->title . " has";
            $approvalEmailContent = "<br /><b>Assigned Team</b>: " . $config->team->title;
        } else {
            if (count($checklist_user) == 1) {
                $userNotificationContent = $checklist_user[0]->name . " has";
                $approvalEmailContent = "<br /><b>Assigned Staff</b>: " . $checklist_user[0]->name;
            }
            if (count($checklist_user) == 2) {
                $userNotificationContent = $checklist_user[0]->name . ", " . $checklist_user[1]->name . " have";
                $approvalEmailContent = "<br /><b>Assigned Staff</b>: " . $checklist_user[0]->name . ", " . $checklist_user[1]->name;
            }
            if (count($checklist_user) > 2) {
                $userNotificationContent = $checklist_user[0]->name . ", " . $checklist_user[1]->name . " and more have";
                $approvalEmailContent = "<br /><b>Assigned Staff</b>: " . $checklist_user[0]->name . ", " . $checklist_user[1]->name . " and more";
            }
        }
        Notification::send($checklist_user, new GeneralNotification($config, 'Config', 'POST', $userNotificationContent . " been assigned a new checklist $task->name. Check your email for details. ", $subject, $content));
        foreach ($checklist_user as  $model) {
            $this->generalService->sendEmail($model, $subject, $content);
            $managerContent = "I trust this email finds you well. I wanted to inform you that a checklist has been successfully assigned to a staff member/Team on MyCheX. The details are as follows:<br /> <b>Checklist Title: </b>" . $task->name . $approvalEmailContent . "<br /> <b>Peroid Type:</b> " . $config->repeat . "<br /><br />The completion of this checklist is integral to us. Your prompt approval would be highly appreciated.<br /><br />If you have any questions or need further information, please feel free to reach out.<br /><br />Thank you for your attention to this matter.";
        }
        $config->staffPivot()->createMany($staff);
        $notificationContent = $task->name . ", has been assigned to your team or specific members. Check your email for details and coordinate with your team for prompt action.";

        $approvers = [];
        foreach ($data['approval'] as $user) {
            $approvers[] = [
                'user_id' => $user,
                'type' => 'approver',
            ];
        }
        $approver = User::whereIn('id', $data['approval'])->get();
        $managerTitle = "Approval Request for Checklist";
        foreach ($approver as  $approverModel) {
            $this->generalService->sendEmail($approverModel, $managerTitle, $managerContent);
        }
        Notification::send(Auth::user(), new GeneralNotification($config, 'Config', 'POST', $notificationContent, $subject, $content));
        $this->generalService->sendFCMNotification($checklist_user, 'Assignment: Checklist on MyCheX', 'A checklist has been assigned to you on MyCheX');

        $config->approversPivot()->createMany($approvers);
        return $config->load(['staff', 'approvers']);
    }

    public function update($data, $id)
    {
        $config = Config::findOrFail($id);
        $this->configService->sendConfigUpdateEmail($data, $config);
        $config->update($data);

        $staff = [];
        // $task = Task::where('id', $config['task_id'])->first();

        // $content = "I am writing to inform you that a checklist has been assigned to you on MyCheX.<br /><br /> <b>Checklist Details:</b><br /><b>Checklist Title: </b>" . $task->name . '<br /> <b> Description: </b>' . $task->description . ' <br /><br />Your attention to completing this checklist is highly valued. If you have any questions or require clarification regarding the assigned checklist, please do not hesitate to reach out <a href="mailto:info@mychex.co.uk">info@mychex.co.uk</a>';
        foreach ($data['staff_id'] as $user) {
            $staff[] = [
                'user_id' => $user,
            ];
        }
        // $checklist_user = User::whereIn('id', $staff)->get();

        $config->staffPivot()->delete();
        $config->staffPivot()->createMany($staff);

        $approvers = [];
        foreach ($data['approval'] as $user) {
            $approvers[] = [
                'user_id' => $user,
                'type' => 'approver',
            ];
        }
        // $approver = User::whereIn('id', $data['approval'])->get();
        $config->approversPivot()->delete();
        $config->approversPivot()->createMany($approvers);
        return $config->load(['staff', 'approvers']);
    }

    private function configExist($date, $config)
    {
        if ($config->repeat == 'daily') {
            $add_day = $date->addDays($config->repeat_count);
        } else if ($config->repeat == 'weekly') {
            $add_day = $date->addDays($config->repeat_count * 7);
        } elseif ($config->repeat == 'monthly') {
            $add_day = $date->addMonths($config->repeat_count);
        } elseif ($config->repeat == 'yearly') {
            $add_day = $date->addYear($config->repeat_count);
        }
        return $add_day;
    }

    public function refainChecklist($id)
    {
        $config = Config::where('task_id', $id)->where('company_id', auth()->user()->company_id)->latest('created_at')->first();
        if ($config) {
            $add_day = $this->configExist(Carbon::createFromFormat('Y-m-d', $config->repeat_start_dd), $config);
            if ($add_day->format('Y-m-d') > date('Y-m-d')) {
                return "Kindly refrain from duplicating the checklist.Perform the task after the " . $add_day->format('Y-m-d'); //Will throw an HTTP exception with code 403
            }
        }
        return '';
    }
}
