<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
// use App\Models\Admin\CheckList;
// use App\Models\Admin\CheckListAssignee;
// use App\Models\Admin\MyCheckListConfig;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

/**
 * Class RiskConfigService
 * @package App\Services
 */
class RiskConfigService
{
    public function __construct(private GeneralService $generalService)
    {
    }

    // public function index($request)
    // {
    //     $searchCols = ['name', 'repeat', 'repeat_count'];
    //     $relationalCols = [
    //         'checklist' => ['name', 'priority', 'status', 'ref_id'],
    //     ];
    //     $relationalWhere =  [
    //         'checklist' => [
    //             ['key' => 'type', 'operator' => '=', 'value' => 'risk_template'],
    //         ]
    //     ];

    //     $data = MyCheckListConfig::with(['checklist', 'company', 'previous_checklist_daily']);

    //     $data = $this->generalService->handleRelationalWhere($data, $relationalWhere);
    //     $data = $this->generalService->handleSearch($request['searchText'], $data, $searchCols, '', $relationalCols);

    //     if ($request['perpage'] == 'All') {
    //         return $this->generalService->handleAllData($request, $data);
    //     } else {
    //         return $this->generalService->handlePagination($request, $data);
    //     }
    // }

    public function store($data)
    {
        // $parent = CheckList::where('id', $data['checklist_id'])->first();
        // if ($parent->type != 'risk_template') {
        //     abort(422, 'Hazard Assessment list is required.');
        // }
        // if ($parent->status != 'active') {
        //     abort(422, 'Hazard Assessment is not available for scheduling.');
        // }


        // return $this->assignConfigApprovals($data, $parent);
    }

    // private function sendEmail($userId, $message)
    // {
    //     $user = User::where('id', $userId)->first();
    //     $email_data = [
    //         "name"          =>  $user->first_name,
    //         "email"         =>  $user->email,
    //         "subject"       =>  "New Checklist Assigned to You on Chex!",
    //         "body"          =>  $message,
    //         'button_url'    =>  'http://localhost:9000/',
    //         'button_text'   =>  "Login",
    //         'logo'          => (Auth::user()->company->photo != null) ? Auth::user()->company->photo->path : config('app.aws_url') . "public/logo.png",
    //     ];
    //     return dispatch(new SendEmailJob($email_data));
    // }

//     public function assignConfigApprovals($data, $parent)
//     {
//         $auth = Auth::user();
//         $data['user_id'] = $auth->id;
//         $data['company_id'] = $auth->company_id;
//         $checklist = MyCheckListConfig::create($data);
//         \Artisan::call('app:store-daily-checklist');
//         if (count($data['staff_id']) > 0) {
//             foreach ($data['staff_id'] as $user) {
//                 $item['user_id'] = $user;
//                 $item['checklist_config_id'] = $checklist->id;
//                 $item['is_active'] = 0;
//                 CheckListAssignee::create($item);
//                 //                 $this->sendEmail($user, $parent->name . ", Exciting news! A new checklist has been assigned to you on Chex. Your manager has created this checklist to streamline and organize daily tasks and routines. You can now access and manage your checklist by logging into your Chex account. <br /> Checklist Title: : ".$checklist->check ." <br /> Log in now to review and start checking off your tasks. If you have any questions or need further guidance, feel free to reach out to your manager.
//                 // Thank you for your commitment to excellence. Let's make every task a step towards success!");
//                 $checklist_user = User::where('id', $user)->first();

//                 Notification::send($checklist_user, new GeneralNotification($checklist, 'MyChecklistConfig', 'POST', $parent->name . ", a checklist has been assigned to you.", "New Checklist Assigned to You on Chex!", $parent->name . ", Exciting news! A new checklist has been assigned to you on Chex. Your manager has created this checklist to streamline and organize daily tasks and routines. You can now access and manage your checklist by logging into your Chex account. <br /> Checklist Title: : " . $checklist->check . " <br /> Log in now to review and start checking off your tasks. If you have any questions or need further guidance, feel free to reach out to your manager.
// Thank you for your commitment to excellence. Let's make every task a step towards success!"));
//             }
//         }

//         foreach ($data['approval'] as $approval) {
//             CheckListAssignee::create([
//                 'user_id' => $approval,
//                 'checklist_config_id' => $checklist->id,
//                 'is_active' =>  1
//             ]);
//             //             $this->sendEmail($approval,$user, $parent->name . ", Exciting news! A new checklist has been assigned to you on Chex. Your manager has created this checklist to streamline and organize daily tasks and routines. You can now access and manage your checklist by logging into your Chex account. <br /> Checklist Title: : ".$checklist->check ." <br /> Log in now to review and start checking off your tasks. If you have any questions or need further guidance, feel free to reach out to your manager.
//             // Thank you for your commitment to excellence. Let's make every task a step towards success!");
//             $checklist_approval = User::where('id', $approval)->first();
//             Notification::send($checklist_approval, new GeneralNotification($checklist, 'MyChecklistConfig', 'POST', 'You have been designated as the checklist approver of ' . $checklist->name . '.', "New Checklist Assigned to You on Chex!", $parent->name . ", Exciting news! A new checklist has been assigned to you on Chex. Your manager has created this checklist to streamline and organize daily tasks and routines. You can now access and manage your checklist by logging into your Chex account. <br /> Checklist Title: : " . $checklist->check . " <br /> Log in now to review and start checking off your tasks. If you have any questions or need further guidance, feel free to reach out to your manager.
// Thank you for your commitment to excellence. Let's make every task a step towards success!"));
//         }
//         return $checklist;
//     }
}
